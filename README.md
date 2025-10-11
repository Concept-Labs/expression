# Expression

[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](https://opensource.org/licenses/Apache-2.0)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-8892BF.svg)](https://www.php.net/)

A flexible and powerful PHP library for building and composing expressions with decorators. Perfect for constructing SQL queries, configuration strings, or any text-based DSL in a programmatic and maintainable way.

## Features

- 🔧 **Fluent API** - Chain methods for clean, readable code
- 🎨 **Decorator Pattern** - Transform expressions with decorators
- 🏗️ **Composable** - Nest expressions within expressions
- 🔄 **Immutable Context** - Safe context interpolation
- 🧩 **Extensible** - Easy to extend with custom decorators
- ✨ **Type Safe** - Full PHP 8.2+ type hints
- 🧪 **Well Tested** - Comprehensive test coverage with PHPUnit and Pest

## Installation

Install via Composer:

```bash
composer require concept-labs/expression
```

## Quick Start

```php
use Concept\Expression\Expression;
use Concept\Expression\Expression;

// Create an expression
$expression = new Expression();

// Build a simple expression
$expression->push('SELECT', 'id', 'name', 'FROM', 'users');
echo $expression; // Output: SELECT id name FROM users

// Use decorators for more control
$columns = (new Expression())
    ->push('id', 'name', 'email')
    ->wrapItem('`')
    ->join(', ');

$query = (new Expression())
    ->push('SELECT', $columns, 'FROM', 'users');
    
echo $query; // Output: SELECT `id`, `name`, `email` FROM users
```

## Basic Usage

### Creating Expressions

```php
$expr = new Expression();

// Add expressions
$expr->push('SELECT', 'column');
$expr->push('FROM', 'table');

// Add to beginning
$expr->unshift('EXPLAIN');

echo $expr; // Output: EXPLAIN SELECT column FROM table
```

### Nested Expressions

```php
$columns = (new Expression())
    ->push('id', 'name')
    ->join(', ');

$mainExpr = (new Expression())
    ->push('SELECT', $columns, 'FROM', 'users');

echo $mainExpr; // Output: SELECT id, name FROM users
```

### Using Decorators

#### Wrap Expressions

```php
$expr = (new Expression())
    ->push('value')
    ->wrap('(', ')');
    
echo $expr; // Output: (value)
```

#### Wrap Items

```php
$expr = (new Expression())
    ->push('id', 'name', 'email')
    ->wrapItem('`')
    ->join(', ');
    
echo $expr; // Output: `id`, `name`, `email`
```

#### Custom Decorators

```php
// Item decorator - applied to each item
$expr = (new Expression())
    ->push('select', 'from', 'where')
    ->decorateItem(fn($item) => strtoupper($item));

echo $expr; // Output: SELECT FROM WHERE

// Expression decorator - applied to final result
$expr = (new Expression())
    ->push('column')
    ->decorate(fn($str) => "SELECT $str FROM users");

echo $expr; // Output: SELECT column FROM users

// Join decorator - custom join logic
$expr = (new Expression())
    ->push('condition1', 'condition2')
    ->decorateJoin(fn($items) => implode(' AND ', $items));

echo $expr; // Output: condition1 AND condition2
```

### Context Interpolation

```php
$template = (new Expression())
    ->push('SELECT', '{column}', 'FROM', '{table}');

$concrete = $template->withContext([
    'column' => 'name',
    'table' => 'users'
]);

echo $concrete; // Output: SELECT name FROM users
echo $template; // Output: SELECT {column} FROM {table} (unchanged)
```

### Clone and Prototype

```php
$base = (new Expression())
    ->push('SELECT', 'id');

// Clone creates independent copy
$clone = clone $base;
$clone->push('FROM', 'users');

echo $base;  // Output: SELECT id
echo $clone; // Output: SELECT id FROM users

// Prototype is alias for clone
$proto = $base->prototype();
```

### Reset

```php
$expr = (new Expression())
    ->push('SELECT', 'column')
    ->type('select');

$expr->reset(); // Clears everything

echo $expr; // Output: (empty string)
```

## Advanced Usage

### Building Complex SQL Queries

```php
// Build a complex SELECT query
$columns = (new Expression())
    ->push('u.id', 'u.name', 'u.email', 'p.title')
    ->wrapItem('`')
    ->join(', ');

$joins = (new Expression())
    ->push('JOIN', 'posts', 'p', 'ON', 'u.id = p.user_id');

$where = (new Expression())
    ->push('u.active = 1', 'p.published = 1')
    ->join(' AND ')
    ->wrap('WHERE ', '');

$query = (new Expression())
    ->push('SELECT', $columns)
    ->push('FROM', 'users', 'u')
    ->push($joins)
    ->push($where);

echo $query;
// Output: SELECT `u.id`, `u.name`, `u.email`, `p.title` FROM users u JOIN posts p ON u.id = p.user_id WHERE u.active = 1 AND p.published = 1
```

### Multiple Decorator Layers

```php
$expr = (new Expression())
    ->push('a', 'b', 'c')
    ->decorateItem(fn($item) => strtoupper($item))  // Items: A, B, C
    ->decorateItem(fn($item) => "`$item`")          // Items: `A`, `B`, `C`
    ->join(', ')                                     // Join: `A`, `B`, `C`
    ->decorate(fn($str) => "SELECT $str")            // Wrap: SELECT `A`, `B`, `C`
    ->decorate(fn($str) => "$str FROM table");       // Wrap: SELECT `A`, `B`, `C` FROM table

echo $expr; // Output: SELECT `A`, `B`, `C` FROM table
```

## API Reference

For detailed API documentation, see [docs/api-reference.md](docs/api-reference.md).

### Main Classes

- **Expression** - Main expression class
- **ExpressionInterface** - Interface for expressions
- **DecoratorManager** - Manages decorators for expressions
- **Decorator** - Static helper methods for common decorators

### Key Methods

#### Expression Methods

- `push(...$expressions)` - Add expressions to the end
- `unshift(...$expressions)` - Add expressions to the beginning
- `wrap($left, $right = null)` - Wrap the entire expression
- `wrapItem($left, $right = null)` - Wrap each item
- `join($separator)` - Set the join separator
- `decorate(callable ...$decorator)` - Add expression decorator
- `decorateItem(callable ...$decorator)` - Add item decorator
- `decorateJoin(callable $decorator)` - Set join decorator
- `withContext(array $context)` - Create new expression with context
- `reset()` - Reset the expression
- `type(string $type)` - Set expression type
- `isEmpty()` - Check if expression is empty
- `prototype()` - Create a clone

## Testing

The package includes comprehensive tests using both PHPUnit and Pest.

```bash
# Run all tests with Pest
composer test

# Run PHPUnit tests
composer test:phpunit

# Run with coverage
composer test:coverage
```

## Contributing

Contributions are welcome! Please see [docs/contributing.md](docs/contributing.md) for details.

## License

This package is licensed under the Apache License 2.0. See [LICENSE](LICENSE) file for details.

## Credits

- **Viktor Halytskyi** - Original author
- Part of the [Concept Labs](https://github.com/Concept-Labs) ecosystem

## Links

- [Extended Documentation](docs/README.md)
- [Architecture Overview](docs/architecture.md)
- [Examples](docs/examples.md)
- [API Reference](docs/api-reference.md)
