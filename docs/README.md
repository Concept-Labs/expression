# Expression Library - Extended Documentation

Welcome to the extended documentation for the Concept Labs Expression library. This library provides a flexible and powerful way to build and compose expressions programmatically.

## Table of Contents

1. [Architecture Overview](architecture.md) - Learn about the design and architecture
2. [API Reference](api-reference.md) - Detailed API documentation
3. [Examples](examples.md) - Practical examples and use cases
4. [Contributing](contributing.md) - How to contribute to the project

## What is Expression?

The Expression library is a PHP package that allows you to build complex text-based expressions (like SQL queries, configuration strings, or DSL statements) in a programmatic, composable, and maintainable way.

### Core Concepts

#### 1. Expressions

An expression is a collection of items (strings, scalars, or other expressions) that can be converted to a string representation. Think of it as a sophisticated string builder with powerful composition capabilities.

#### 2. Decorators

Decorators transform how expressions are rendered. There are three types:
- **Item Decorators** - Transform each item in the expression
- **Join Decorators** - Control how items are joined together
- **Expression Decorators** - Transform the final expression string

#### 3. Composition

Expressions can contain other expressions, allowing you to build complex hierarchical structures that remain readable and maintainable.

## Why Use Expression?

### Problem: Building Dynamic Queries

Traditional string concatenation for building dynamic queries is error-prone:

```php
// ❌ Hard to read, error-prone, hard to maintain
$query = "SELECT " . implode(', ', array_map(fn($c) => "`$c`", $columns)) . 
         " FROM " . $table . 
         (!empty($where) ? " WHERE " . implode(' AND ', $where) : "");
```

### Solution: Composable Expressions

With the Expression library:

```php
// ✅ Clear, maintainable, composable
$columns = (new Expression(new DecoratorManager()))
    ->push(...$columns)
    ->wrapItem('`')
    ->join(', ');

$query = (new Expression(new DecoratorManager()))
    ->push('SELECT', $columns, 'FROM', $table);

if (!empty($where)) {
    $whereExpr = (new Expression(new DecoratorManager()))
        ->push(...$where)
        ->join(' AND ')
        ->wrap('WHERE ', '');
    $query->push($whereExpr);
}
```

## Key Features

### Fluent Interface

Chain method calls for readable code:

```php
$expr = (new Expression(new DecoratorManager()))
    ->push('value1', 'value2')
    ->join(', ')
    ->wrap('(', ')');
```

### Type Safety

Full PHP 8.2+ type hints ensure compile-time safety:

```php
public function push(...$expressions): static
public function wrap(
    string|Stringable|ExpressionInterface $left, 
    string|Stringable|ExpressionInterface|null $right = null
): static
```

### Immutability Where It Matters

Context interpolation returns new instances, preserving the original:

```php
$template = $expr->push('SELECT {column}');
$concrete = $template->withContext(['column' => 'name']);
// $template remains unchanged, $concrete has interpolated values
```

### Extensibility

Easy to create custom decorators:

```php
// Custom decorator to add SQL comments
$expr->decorate(fn($sql) => "/* Generated */\n$sql");
```

## Getting Started

1. **Installation**: `composer require concept-labs/expression`
2. **Quick Start**: See the [README](../README.md) for basic examples
3. **Examples**: Check [examples.md](examples.md) for real-world use cases
4. **API Reference**: See [api-reference.md](api-reference.md) for detailed API docs

## Architecture

The library is built around a few core components:

```
Expression (main class)
├── ExpressionInterface (contract)
├── DecoratorManager (manages decorators)
│   ├── DecoratorManagerInterface
│   └── Decorator (static helpers)
└── Exception classes
```

For detailed architecture information, see [architecture.md](architecture.md).

## Use Cases

The Expression library is perfect for:

- **Query Builders** - Build SQL, NoSQL, or other query languages
- **Code Generation** - Generate code snippets or templates
- **Configuration** - Build complex configuration strings
- **DSL Creation** - Create domain-specific languages
- **Template Systems** - Build flexible template systems
- **String Composition** - Any scenario requiring complex string building

## Performance Considerations

The library is designed for developer experience and maintainability rather than raw performance. However:

- Expressions are lazy - decorators are only applied when converting to string
- Cloning is explicit, giving you control over when copies are made
- No magic methods or reflection - straightforward PHP code

For high-performance scenarios, consider:
- Caching expression results
- Building expressions once and reusing with different contexts
- Using prototypes to avoid rebuilding common structures

## Next Steps

- Read the [Architecture Overview](architecture.md) to understand the design
- Check out the [Examples](examples.md) for practical use cases
- Review the [API Reference](api-reference.md) for detailed documentation
- See [Contributing](contributing.md) to contribute to the project
