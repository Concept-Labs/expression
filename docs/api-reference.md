# API Reference

Complete API documentation for the Expression library.

## Table of Contents

- [Expression Class](#expression-class)
- [ExpressionInterface](#expressioninterface)
- [DecoratorManager Class](#decoratormanager-class)
- [DecoratorManagerInterface](#decoratormanagerinterface)
- [Decorator Class](#decorator-class)
- [Exception Classes](#exception-classes)

---

## Expression Class

**Namespace:** `Concept\Expression`

**Implements:** `ExpressionInterface`

The main class for creating and manipulating expressions.

### Constructor

```php
public function __construct(DecoratorManagerInterface $decoratorManager)
```

Creates a new expression instance.

**Parameters:**
- `$decoratorManager` - The decorator manager to use

**Example:**
```php
use Concept\Expression\Expression;
use Concept\Expression\Decorator\DecoratorManager;

$expression = new Expression(new DecoratorManager());
```

### Methods

#### push()

```php
public function push(...$expressions): static
```

Add one or more expressions to the end of the expression.

**Parameters:**
- `...$expressions` - Variable number of scalar values or ExpressionInterface instances

**Returns:** `static` - Returns $this for method chaining

**Throws:** `InvalidArgumentException` - If any expression is not scalar or ExpressionInterface

**Example:**
```php
$expr->push('SELECT', 'column1', 'column2');
$expr->push('FROM', 'table');
```

**Notes:**
- Empty values and empty expressions are automatically skipped
- Accepts scalars (string, int, float, bool) and ExpressionInterface instances

---

#### unshift()

```php
public function unshift(...$expressions): static
```

Add one or more expressions to the beginning of the expression.

**Parameters:**
- `...$expressions` - Variable number of expressions

**Returns:** `static` - Returns $this for method chaining

**Example:**
```php
$expr->push('column', 'FROM', 'table');
$expr->unshift('SELECT'); // Now: SELECT column FROM table
```

---

#### wrap()

```php
public function wrap(
    string|Stringable|ExpressionInterface $left, 
    string|Stringable|ExpressionInterface|null $right = null
): static
```

Wrap the entire expression with left and right delimiters.

**Parameters:**
- `$left` - Left wrapper (or both if $right is null)
- `$right` - Optional right wrapper

**Returns:** `static` - Returns $this for method chaining

**Example:**
```php
$expr->push('value')->wrap('(', ')');  // (value)
$expr->push('value')->wrap('"');       // "value"
```

---

#### wrapItem()

```php
public function wrapItem(
    string|Stringable|ExpressionInterface $left, 
    string|Stringable|ExpressionInterface|null $right = null
): static
```

Wrap each item in the expression with left and right delimiters.

**Parameters:**
- `$left` - Left wrapper (or both if $right is null)
- `$right` - Optional right wrapper

**Returns:** `static` - Returns $this for method chaining

**Example:**
```php
$expr->push('a', 'b', 'c')->wrapItem('`');  // `a` `b` `c`
$expr->push('a', 'b')->wrapItem('(', ')');  // (a) (b)
```

---

#### join()

```php
public function join(
    string|Stringable|ExpressionInterface $separator
): static
```

Set the separator for joining items.

**Parameters:**
- `$separator` - The separator to use

**Returns:** `static` - Returns $this for method chaining

**Example:**
```php
$expr->push('a', 'b', 'c')->join(', ');  // a, b, c
$expr->push('a', 'b')->join(' AND ');    // a AND b
```

**Default:** Space character `' '`

---

#### decorate()

```php
public function decorate(callable ...$decorator): static
```

Add one or more decorators to transform the final expression string.

**Parameters:**
- `...$decorator` - One or more callable decorators

**Returns:** `static` - Returns $this for method chaining

**Example:**
```php
$expr->decorate(fn($str) => strtoupper($str));
$expr->decorate(
    fn($str) => strtoupper($str),
    fn($str) => "[$str]"
);
```

**Decorator Signature:**
```php
function(string $expressionString): string
```

---

#### decorateItem()

```php
public function decorateItem(callable ...$decorator): static
```

Add one or more decorators to transform each item.

**Parameters:**
- `...$decorator` - One or more callable decorators

**Returns:** `static` - Returns $this for method chaining

**Example:**
```php
$expr->push('a', 'b')->decorateItem(fn($item) => strtoupper($item));
// Result: A B
```

**Decorator Signature:**
```php
function(string $itemString): string
```

---

#### decorateJoin()

```php
public function decorateJoin(callable $decorator): ExpressionInterface
```

Set a custom join decorator.

**Parameters:**
- `$decorator` - The join decorator callable

**Returns:** `ExpressionInterface` - Returns $this for method chaining

**Example:**
```php
$expr->decorateJoin(fn($items) => implode(' OR ', $items));
```

**Decorator Signature:**
```php
function(array $items): string
```

---

#### withContext()

```php
public function withContext(array $context): static
```

Create a new expression with context variables for interpolation.

**Parameters:**
- `$context` - Associative array of context variables

**Returns:** `static` - A new cloned expression with the context

**Example:**
```php
$template = $expr->push('SELECT', '{column}', 'FROM', '{table}');
$concrete = $template->withContext([
    'column' => 'name',
    'table' => 'users'
]);
// $template is unchanged, $concrete has interpolated values
```

**Notes:**
- Only scalar values are interpolated
- Uses `{key}` syntax in expressions
- Returns a new instance (original unchanged)

---

#### reset()

```php
public function reset(): static
```

Reset the expression to empty state.

**Returns:** `static` - Returns $this for method chaining

**Example:**
```php
$expr->push('a', 'b')->reset();
// $expr is now empty
```

**Notes:**
- Clears all expressions
- Clears context
- Resets type
- Does not reset decorator manager

---

#### type()

```php
public function type(string $type): static
```

Set the expression type identifier.

**Parameters:**
- `$type` - Type identifier string

**Returns:** `static` - Returns $this for method chaining

**Example:**
```php
$expr->type('select');
```

**Notes:**
- Used primarily for debugging
- Displayed in `getDebugString()`

---

#### isEmpty()

```php
public function isEmpty(): bool
```

Check if the expression is empty.

**Returns:** `bool` - True if no expressions have been added

**Example:**
```php
$expr = new Expression(new DecoratorManager());
$expr->isEmpty(); // true
$expr->push('value');
$expr->isEmpty(); // false
```

---

#### prototype()

```php
public function prototype(): static
```

Create a clone of the expression.

**Returns:** `static` - A new cloned instance

**Example:**
```php
$base = $expr->push('SELECT');
$variant1 = $base->prototype()->push('* FROM users');
$variant2 = $base->prototype()->push('count(*) FROM products');
```

**Notes:**
- Alias for `clone`
- Clones decorator manager (which resets it)
- Shallow copies expression items and context

---

#### __toString()

```php
public function __toString(): string
```

Convert the expression to a string.

**Returns:** `string` - The rendered expression

**Example:**
```php
$expr->push('SELECT', 'column');
echo $expr; // Calls __toString()
$str = (string)$expr; // Explicit cast
```

---

#### getDebugString()

```php
public function getDebugString(): string
```

Get a debug representation of the expression with type information.

**Returns:** `string` - Debug string with type wrapper

**Example:**
```php
$expr->push('value')->type('test');
echo $expr->getDebugString(); // {TEST:value}
```

---

#### getIterator()

```php
public function getIterator(): Traversable
```

Get an iterator for the expression items.

**Returns:** `Traversable` - Iterator over expression items

**Example:**
```php
foreach ($expr as $item) {
    echo $item;
}
```

---

#### getExpressions()

```php
public function getExpressions(): array
```

Get the raw expressions array.

**Returns:** `array` - Array of expression items

**Example:**
```php
$items = $expr->getExpressions();
```

---

## ExpressionInterface

**Namespace:** `Concept\Expression`

**Extends:** `PrototypeInterface`, `ResetableInterface`, `IteratorAggregate`, `Stringable`

Interface defining the contract for Expression implementations.

### Methods

All methods from Expression class are defined in this interface. See Expression class documentation for details.

---

## DecoratorManager Class

**Namespace:** `Concept\Expression\Decorator`

**Implements:** `DecoratorManagerInterface`, `PrototypeInterface`

Manages decorators for expressions.

### Methods

#### addDecorator()

```php
public function addDecorator(callable ...$decorator): static
```

Add one or more expression decorators.

**Parameters:**
- `...$decorator` - One or more decorator callables

**Returns:** `static` - Returns $this for method chaining

---

#### addItemDecorator()

```php
public function addItemDecorator(callable ...$decorator): static
```

Add one or more item decorators.

**Parameters:**
- `...$decorator` - One or more decorator callables

**Returns:** `static` - Returns $this for method chaining

---

#### setJoinDecorator()

```php
public function setJoinDecorator(callable $decorator): static
```

Set the join decorator.

**Parameters:**
- `$decorator` - The join decorator callable

**Returns:** `static` - Returns $this for method chaining

---

#### join()

```php
public function join(string|Stringable|ExpressionInterface $separator): static
```

Set the join separator (shortcut for setJoinDecorator).

**Parameters:**
- `$separator` - The separator

**Returns:** `static` - Returns $this for method chaining

---

#### wrap()

```php
public function wrap(
    string|Stringable|ExpressionInterface $left, 
    string|Stringable|ExpressionInterface|null $right = null
): static
```

Add a wrapping decorator to the expression.

**Parameters:**
- `$left` - Left wrapper
- `$right` - Optional right wrapper

**Returns:** `static` - Returns $this for method chaining

---

#### wrapItem()

```php
public function wrapItem(
    string|Stringable|ExpressionInterface $left, 
    string|Stringable|ExpressionInterface|null $right = null
): static
```

Add a wrapping decorator to each item.

**Parameters:**
- `$left` - Left wrapper
- `$right` - Optional right wrapper

**Returns:** `static` - Returns $this for method chaining

---

#### applyDecorations()

```php
public function applyDecorations(ExpressionInterface $expression): string
```

Apply all decorators to the expression.

**Parameters:**
- `$expression` - The expression to decorate

**Returns:** `string` - The decorated string

---

#### reset()

```php
public function reset(): static
```

Reset all decorators.

**Returns:** `static` - Returns $this for method chaining

---

#### prototype()

```php
public function prototype(): static
```

Create a clone of the decorator manager.

**Returns:** `static` - A new cloned instance

**Notes:**
- The clone is automatically reset

---

## DecoratorManagerInterface

**Namespace:** `Concept\Expression\Decorator`

**Extends:** `ResetableInterface`

Interface defining the contract for DecoratorManager implementations.

---

## Decorator Class

**Namespace:** `Concept\Expression\Decorator`

**Implements:** `DecoratorInterface`

Static helper class providing common decorator factories.

### Static Methods

#### wrapper()

```php
public static function wrapper(
    string|Stringable|ExpressionInterface $left, 
    string|Stringable|ExpressionInterface|null $right = null
): callable
```

Create a wrapping decorator.

**Parameters:**
- `$left` - Left wrapper (or both if $right is null)
- `$right` - Optional right wrapper

**Returns:** `callable` - A decorator function

**Throws:** `InvalidArgumentException` - If parameters are invalid

**Example:**
```php
$wrapper = Decorator::wrapper('(', ')');
echo $wrapper('value'); // (value)

$expr->decorateItem(Decorator::wrapper('`'));
```

---

#### joiner()

```php
public static function joiner(
    string|Stringable|ExpressionInterface $separator
): callable
```

Create a joining decorator.

**Parameters:**
- `$separator` - The separator to join with

**Returns:** `callable` - A join decorator function

**Throws:** `InvalidArgumentException` - If separator is invalid

**Example:**
```php
$joiner = Decorator::joiner(', ');
echo $joiner(['a', 'b', 'c']); // a, b, c

$expr->decorateJoin(Decorator::joiner(' AND '));
```

---

## Exception Classes

### InvalidArgumentException

**Namespace:** `Concept\Expression\Exception`

**Extends:** `ExpressionException`

Thrown when invalid arguments are provided.

**Examples:**
- Pushing non-scalar, non-ExpressionInterface values
- Invalid decorator parameters

---

### ExpressionException

**Namespace:** `Concept\Expression\Exception`

**Extends:** `Concept\Exception\ConceptException`

**Implements:** `ExpressionExceptionInterface`

Base exception for all Expression library exceptions.

---

### ExpressionExceptionInterface

**Namespace:** `Concept\Expression\Exception`

**Extends:** `Concept\Exception\ConceptExceptionInterface`

Base exception interface for all Expression library exceptions.

---

## Type Definitions

### Decorator Callable Signatures

**Item Decorator:**
```php
callable(string $itemString): string
```

**Expression Decorator:**
```php
callable(string $expressionString): string
```

**Join Decorator:**
```php
callable(array $items): string
```

---

## Constants

The library currently defines no constants.

---

## Best Practices

### 1. Method Chaining

Take advantage of fluent interface:
```php
$expr->push('a', 'b', 'c')
     ->join(', ')
     ->wrap('(', ')');
```

### 2. Decorator Order

Remember the decoration order:
1. Item decorators (each item)
2. Join decorator (combine items)
3. Expression decorators (final result)

### 3. Context Immutability

Always remember `withContext()` returns a new instance:
```php
$template = $expr->push('{value}');
$concrete = $template->withContext(['value' => 'test']);
// Use $concrete, not $template
```

### 4. Reuse Prototypes

Create base expressions and clone them:
```php
$base = (new Expression($manager))->push('SELECT');
$query1 = $base->prototype()->push('* FROM users');
$query2 = $base->prototype()->push('count(*) FROM products');
```

### 5. Type Safety

Always type-hint with interfaces:
```php
function buildQuery(ExpressionInterface $expr): string
{
    return (string)$expr;
}
```
