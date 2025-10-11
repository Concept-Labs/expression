# Architecture Overview

This document describes the architecture and design decisions of the Expression library.

## Design Principles

### 1. Composition Over Inheritance

The library favors composition - expressions contain other expressions rather than extending base classes. This allows for maximum flexibility and reusability.

### 2. Fluent Interface

All mutation methods return `$this` (or `static`), enabling method chaining:

```php
$expr->push('a')->join(', ')->wrap('(', ')');
```

### 3. Separation of Concerns

The library separates three distinct concerns:

- **Expression** - What to render (the data)
- **DecoratorManager** - How to render (the transformations)
- **Decorator** - Reusable transformation helpers

### 4. Lazy Evaluation

Expressions are only converted to strings when needed (when `__toString()` is called). This allows for efficient building and modification.

## Core Components

### Expression Class

The `Expression` class is the main entry point. It implements:

- `ExpressionInterface` - The public contract
- `PrototypeInterface` - Cloning support
- `ResetableInterface` - Reset capability
- `IteratorAggregate` - Iteration over items
- `Stringable` - String conversion

#### Key Responsibilities

1. **Storage** - Stores expression items
2. **Composition** - Manages nested expressions
3. **Delegation** - Delegates decoration to DecoratorManager
4. **Context** - Manages context interpolation

#### State

```php
private array $expressions = [];  // The items
private array $context = [];      // Context for interpolation
private ?string $type = null;     // Optional type identifier
private DecoratorManagerInterface $decoratorManager;
```

### DecoratorManager Class

Manages all decorators for an expression.

#### Types of Decorators

1. **Item Decorators** (`callable[]`)
   - Applied to each item individually
   - Executed first in the rendering pipeline
   - Example: Wrapping each column name with backticks

2. **Join Decorator** (`callable`)
   - Controls how items are joined
   - Executed after item decorators
   - Default: Join with space

3. **Expression Decorators** (`callable[]`)
   - Applied to the final joined string
   - Executed last in the rendering pipeline
   - Example: Wrapping entire expression with parentheses

#### Rendering Pipeline

```
Expression Items
    ↓
Item Decorators (applied to each)
    ↓
Join Decorator (combines items)
    ↓
Expression Decorators (applied to result)
    ↓
Final String
```

### Decorator Class

Static helper class providing common decorator factories:

- `wrapper($left, $right = null)` - Creates a wrapping decorator
- `joiner($separator)` - Creates a joining decorator

These are pure functions that return callables, making them easy to test and reuse.

## Design Patterns Used

### 1. Decorator Pattern

The core pattern of the library. Decorators wrap and transform expressions without modifying their structure.

```php
// Each decorator wraps the previous result
$expr->decorate(fn($s) => strtoupper($s))
     ->decorate(fn($s) => "[$s]");
```

### 2. Builder Pattern

The fluent interface implements the Builder pattern, allowing step-by-step construction:

```php
$query = (new Expression($manager))
    ->push('SELECT')
    ->push($columns)
    ->push('FROM', $table)
    ->push($where);
```

### 3. Prototype Pattern

The `prototype()` method implements the Prototype pattern for cloning:

```php
$base = $expr->prototype();
$variant = $base->push('extra');
```

### 4. Template Method Pattern

The `DecoratorManager::applyDecorations()` method implements a template for the decoration process:

```php
public function applyDecorations(ExpressionInterface $expression): string
{
    $items = $this->decorateItems($expression);      // Step 1
    $join = $this->getJoinDecorator();               // Step 2
    $string = $join($items);                         // Step 3
    foreach ($this->getDecorators() as $decorator) { // Step 4
        $string = $decorator($string);
    }
    return $string;
}
```

### 5. Iterator Pattern

Expressions implement `IteratorAggregate`, allowing iteration over items:

```php
foreach ($expression as $item) {
    // Process each item
}
```

## Data Flow

### Building an Expression

```
User Code
    ↓
Expression::push()
    ↓
Store in $expressions array
    ↓
Return $this (for chaining)
```

### Rendering an Expression

```
echo $expression
    ↓
Expression::__toString()
    ↓
DecoratorManager::applyDecorations($expression)
    ↓
[For each item: Apply item decorators]
    ↓
[Join items with join decorator]
    ↓
[Apply expression decorators]
    ↓
Expression::interpolate($string)
    ↓
Return final string
```

## Context Interpolation

Context interpolation happens at the very end of the rendering pipeline:

```php
protected function interpolate(string $expression, array $defaults = []): string
{
    $replacements = [];
    $context = array_merge($defaults, $this->getContext());
    foreach ($context as $key => $value) {
        if (is_scalar($value)) {
            $replacements["{{$key}}"] = $value;
        }
    }
    return strtr($expression, $replacements);
}
```

**Key Points:**
- Only scalar values are interpolated
- Uses `{key}` syntax
- Merges with defaults
- Applied after all decorators

## Cloning Behavior

### Expression Cloning

When an expression is cloned:

```php
public function __clone()
{
    $this->decoratorManager = clone $this->decoratorManager;
}
```

- The decorator manager is also cloned
- Expression items are shallow-copied
- Context is shallow-copied
- Type is preserved

### DecoratorManager Cloning

When a decorator manager is cloned:

```php
public function __clone()
{
    $this->reset();
}
```

- **Important**: The manager is reset on clone
- This prevents decorator pollution when cloning expressions
- Cloned expressions start with a clean slate for decorators

## Extension Points

The library can be extended in several ways:

### 1. Custom Decorators

Create custom decorator functions:

```php
$sqlComment = fn($str) => "/* Generated */\n$str";
$expr->decorate($sqlComment);
```

### 2. Custom Expression Types

Extend the Expression class:

```php
class SqlExpression extends Expression
{
    public function select(...$columns): static
    {
        return $this->push('SELECT', ...$columns);
    }
    
    public function from(string $table): static
    {
        return $this->push('FROM', $table);
    }
}
```

### 3. Custom Decorator Managers

Implement `DecoratorManagerInterface`:

```php
class CachingDecoratorManager implements DecoratorManagerInterface
{
    private array $cache = [];
    
    public function applyDecorations(ExpressionInterface $expression): string
    {
        $key = $this->getCacheKey($expression);
        if (!isset($this->cache[$key])) {
            $this->cache[$key] = parent::applyDecorations($expression);
        }
        return $this->cache[$key];
    }
}
```

## Thread Safety

The library is **not thread-safe** by default:

- Expressions maintain mutable state
- Decorator managers maintain mutable state
- No internal locking mechanisms

For concurrent usage:
- Create separate expression instances per thread
- Use immutable patterns (always create new instances)
- Consider external synchronization if sharing instances

## Memory Considerations

### Memory Usage

- Each expression stores an array of items
- Nested expressions create object graphs
- Decorator callables may capture variables

### Optimization Strategies

1. **Reuse Prototypes**
   ```php
   $base = $expression->prototype();
   // Reuse $base multiple times
   ```

2. **Clear When Done**
   ```php
   $expr->reset(); // Clear all data
   ```

3. **Avoid Deep Nesting**
   - Deep expression nesting increases memory
   - Flatten where possible

## Testing Architecture

The library uses a dual testing approach:

1. **Pest** - For BDD-style tests, better readability
2. **PHPUnit** - For traditional unit tests

Tests are organized by:
- `tests/Unit/` - Unit tests for individual classes
- `tests/Unit/Decorator/` - Decorator-specific tests
- `tests/Unit/PHPUnit/` - Traditional PHPUnit tests

## Performance Characteristics

### Time Complexity

- `push()`: O(1) - Append to array
- `unshift()`: O(n) - Prepend requires shifting
- `__toString()`: O(n*m) where n=items, m=decorators
- Iteration: O(n)

### Space Complexity

- Storage: O(n) where n=number of items
- Nested expressions: O(depth * items)

### Best Practices

1. **Use push() over unshift()** when order doesn't matter
2. **Cache string results** if the same expression is rendered multiple times
3. **Avoid excessive decorator layers** - each layer adds overhead
4. **Use prototypes** to avoid rebuilding common structures

## Comparison with Alternatives

### vs. Simple String Concatenation

**Pros:**
- Type safety
- Composability
- Reusability
- Testability

**Cons:**
- More verbose
- Additional objects
- Learning curve

### vs. Template Engines

**Pros:**
- Programmatic (no separate template files)
- Type-safe
- Better IDE support
- Composable

**Cons:**
- Not suitable for complex templates
- No template caching
- Different paradigm

## Future Considerations

Potential areas for enhancement:

1. **Lazy Evaluation** - Defer decorator application until needed
2. **Caching Layer** - Cache rendered results
3. **Streaming** - Stream large expressions
4. **Validation** - Validate expression structure
5. **Serialization** - Serialize/deserialize expressions
6. **Query Optimization** - Optimize generated queries

## Conclusion

The Expression library provides a solid foundation for building text-based expressions programmatically. Its architecture emphasizes:

- **Simplicity** - Easy to understand core concepts
- **Flexibility** - Extensible through composition and decoration
- **Maintainability** - Clear separation of concerns
- **Type Safety** - Full PHP type system support

Understanding this architecture will help you use the library effectively and extend it for your specific needs.
