# Expression

[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](https://opensource.org/licenses/Apache-2.0)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-8892BF.svg)](https://www.php.net/)

A flexible and powerful PHP library for building and composing expressions with decorators. Build **SQL queries**, **HTML/XML**, **JSON/API responses**, **CLI commands**, **configuration files**, **code generators**, and any text-based output with elegant, fluent syntax.

> **More than a query builder** - Expression is a universal text composition framework that makes complex string building simple, maintainable, and beautiful.

## Features

- 🔧 **Fluent API** - Chain methods for clean, readable code
- 🎨 **Decorator Pattern** - Transform expressions with decorators
- 🏗️ **Composable** - Nest expressions within expressions
- 🔄 **Immutable Context** - Safe context interpolation
- 🧩 **Extensible** - Easy to extend with custom decorators
- ✨ **Type Safe** - Full PHP 8.2+ type hints
- 🧪 **Well Tested** - Comprehensive test coverage with PHPUnit and Pest
- 🚀 **Universal** - Build SQL, HTML, JSON, CLI commands, configs, and more

## Why Expression?

Traditional string building is **error-prone**, **hard to read**, and **difficult to maintain**:

```php
// ❌ Traditional approach - messy and fragile
$query = "SELECT " . implode(', ', array_map(fn($c) => "`$c`", $columns)) . 
         " FROM " . $table . 
         (!empty($where) ? " WHERE " . implode(' AND ', $where) : "");
```

Expression makes it **elegant**, **composable**, and **maintainable**:

```php
// ✅ Expression approach - clean and powerful
$columns = (new Expression())
    ->push(...$columns)
    ->wrapItem('`')
    ->join(', ');

$query = (new Expression())
    ->push('SELECT', $columns, 'FROM', $table);

if (!empty($where)) {
    $query->push((new Expression())
        ->push(...$where)
        ->join(' AND ')
        ->wrap('WHERE ', ''));
}
```

## Installation

Install via Composer:

```bash
composer require concept-labs/expression
```

## Quick Start

```php
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

## 🚀 Power Showcase

### Syntax Sugar - The Magic of `__invoke()`

Expressions are **callable**, enabling ultra-concise syntax:

```php
// Instead of verbose push() calls
$expr = (new Expression())
    ->push('Hello')
    ->push('World');

// Use callable syntax - much cleaner!
$expr = (new Expression())
    ('Hello')
    ('World');

// Build complex expressions elegantly
$banner = (new Expression())
    ('╔════════════════════╗')
    ('║ Welcome to PHP!  ║')
    ('╚════════════════════╝')
    ->join("\n");

echo $banner;
```

### Decorator Stacking - Progressive Transformations

Chain decorators for powerful, layered transformations:

```php
// Transform data through multiple steps
$pipeline = (new Expression())
    ('apple', 'banana', 'cherry')
    ->decorateItem(fn($item) => ucfirst($item))        // Step 1: Capitalize
    ->decorateItem(fn($item) => "🍎 $item")            // Step 2: Add emoji
    ->decorateItem(fn($item) => "[$item]")             // Step 3: Wrap
    ->join(' → ')                                       // Step 4: Join with arrows
    ->decorate(fn($str) => "Fruits: $str")             // Step 5: Add label
    ->decorate(fn($str) => "╭─────────────╮\n│ $str │\n╰─────────────╯"); // Step 6: Box it

echo $pipeline;
// Output:
// ╭─────────────╮
// │ Fruits: [🍎 Apple] → [🍎 Banana] → [🍎 Cherry] │
// ╰─────────────╯
```

### Beyond SQL - Universal Text Building

#### HTML Component Builder

```php
$card = (new Expression())
    ('<div class="card">')
    ('  <h2>Welcome!</h2>')
    ('  <p>Experience the power of Expression</p>')
    ('  <button>Get Started</button>')
    ('</div>')
    ->join("\n");

echo $card;
```

#### CLI Command Builder

```php
// Build complex Docker commands fluently
$docker = (new Expression())
    ('docker', 'run')
    ('-d')
    ('--name', 'my-app')
    ('-p', '8080:80')
    ('-v', '/host/data:/app/data')
    ('-e', 'ENV=production')
    ('nginx:latest');

echo $docker;
// Output: docker run -d --name my-app -p 8080:80 -v /host/data:/app/data -e ENV=production nginx:latest
```

#### JSON API Response Builder

```php
$response = (new Expression())
    ('{"status": "success"')
    ('"data": {"user_id": 123, "name": "Alice"}')
    ('"timestamp": "' . date('c') . '"}')
    ->join(',')
    ->decorate(fn($s) => json_encode(json_decode($s), JSON_PRETTY_PRINT));

echo $response;
```

#### Configuration File Generator

```php
$config = (new Expression())
    ('# Database Configuration')
    ('DB_HOST=localhost')
    ('DB_PORT=5432')
    ('DB_NAME=myapp')
    ('')
    ('# Application Settings')
    ('APP_ENV=production')
    ('APP_DEBUG=false')
    ->join("\n");

echo $config;
```

### Context Interpolation - Reusable Templates

```php
// Create a template with placeholders
$template = (new Expression())
    ('Dear {name},', '', 'Your order #{order_id} is {status}.', '', 'Thank you!')
    ->join("\n");

// Generate multiple outputs from the same template
$email1 = $template->withContext([
    'name' => 'Alice',
    'order_id' => '12345',
    'status' => 'shipped'
]);

$email2 = $template->withContext([
    'name' => 'Bob',
    'order_id' => '67890',
    'status' => 'processing'
]);

// Original template remains unchanged!
echo $template; // Still has {name}, {order_id}, {status}
```

### Composition - Expressions Within Expressions

```php
// Build modular, reusable components
$header = (new Expression())
    ('╔════════════════════╗')
    ('║   {title}         ║')
    ('╚════════════════════╝')
    ->join("\n");

$body = (new Expression())
    ('Content: {content}');

$footer = (new Expression())
    ('───────────────────')
    ('Footer: {footer}')
    ->join("\n");

$page = (new Expression())
    ($header)
    ('')
    ($body)
    ('')
    ($footer)
    ->join("\n")
    ->withContext([
        'title' => 'My App',
        'content' => 'Hello World',
        'footer' => 'v1.0.0'
    ]);

echo $page;
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

### Using __invoke() for Concise Syntax

```php
// You can also use the __invoke() method for a more concise syntax
$expr = new Expression();
$expr('SELECT', 'column')('FROM', 'table');

// Equivalent to:
// $expr->push('SELECT', 'column')->push('FROM', 'table');

echo $expr; // Output: SELECT column FROM table
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

- `__invoke(...$expressions)` - Add expressions (alias for push, enables callable syntax)
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

## More Examples

### 📊 Markdown Table Generator

```php
class MarkdownTable
{
    public static function create(array $headers, array $rows): Expression
    {
        $header = (new Expression())
            (...$headers)
            ->wrapItem('| ', ' ')
            ->join('')
            ->wrap('', '|');

        $separator = (new Expression())
            (...array_fill(0, count($headers), '---'))
            ->wrapItem('| ', ' ')
            ->join('')
            ->wrap('', '|');

        $table = (new Expression())($header)($separator);

        foreach ($rows as $row) {
            $rowExpr = (new Expression())
                (...$row)
                ->wrapItem('| ', ' ')
                ->join('')
                ->wrap('', '|');
            $table->push($rowExpr);
        }

        return $table->join("\n");
    }
}

// Usage
$table = MarkdownTable::create(
    ['Name', 'Age', 'City'],
    [
        ['Alice', '30', 'New York'],
        ['Bob', '25', 'London'],
    ]
);
echo $table;
```

### 🎨 ASCII Art Generator

```php
$banner = (new Expression())
    ('╔═══════════════════════════╗')
    ('║  Expression Library      ║')
    ('║  Build Anything!         ║')
    ('╚═══════════════════════════╝')
    ->join("\n");

echo $banner;
```

### 📝 Log Formatter

```php
class Logger
{
    public static function format(string $level, string $message, array $context = []): Expression
    {
        $icons = ['ERROR' => '❌', 'WARN' => '⚠️', 'INFO' => 'ℹ️', 'SUCCESS' => '✅'];
        
        $log = (new Expression())
            (date('[Y-m-d H:i:s]'))
            ("{$icons[$level]} [$level]")
            ($message);

        if (!empty($context)) {
            $log->push('Context: ' . json_encode($context));
        }

        return $log;
    }
}

echo Logger::format('SUCCESS', 'User logged in', ['user_id' => 123]);
// Output: [2026-01-04 00:00:00] ✅ [SUCCESS] User logged in Context: {"user_id":123}
```

### 🔧 Git Command Builder

```php
class Git
{
    public static function commit(string $message, array $files = []): Expression
    {
        $cmd = (new Expression())('git');

        if (!empty($files)) {
            $cmd('add')(...$files)('&&')('git');
        }

        return $cmd('commit')('-m')("\"$message\"");
    }
}

echo Git::commit('Add new feature', ['src/Feature.php', 'tests/FeatureTest.php']);
// Output: git add src/Feature.php tests/FeatureTest.php && git commit -m "Add new feature"
```

### 🌐 ENV File Builder

```php
class EnvBuilder
{
    private Expression $env;

    public function __construct()
    {
        $this->env = new Expression();
    }

    public function section(string $name): self
    {
        $this->env->push('')
                  ->push("# $name")
                  ->push(str_repeat('-', strlen($name) + 2));
        return $this;
    }

    public function set(string $key, $value): self
    {
        $this->env->push("$key=$value");
        return $this;
    }

    public function build(): string
    {
        return (string)$this->env->join("\n");
    }
}

$config = (new EnvBuilder())
    ->section('Database')
    ->set('DB_HOST', 'localhost')
    ->set('DB_PORT', '5432')
    ->section('Application')
    ->set('APP_ENV', 'production')
    ->build();
```

For **more advanced examples** including:
- 🏗️ HTML/XML Builders
- 🔌 GraphQL Query Builders
- 🐳 Docker Command Composers
- 💻 PHP Code Generators
- 📊 YAML Configuration Builders
- 🧪 Test Data Builders
- 🎯 DSL Creation
- 🎨 Creative Use Cases

See the comprehensive [**Advanced Examples Guide**](docs/advanced-examples.md)!

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
- [**🚀 Advanced Examples**](docs/advanced-examples.md) - **NEW!** Showcase of powerful use cases
- [API Reference](docs/api-reference.md)
