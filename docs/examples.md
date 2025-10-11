# Examples

This document provides practical examples of using the Expression library.

## Table of Contents

1. [Basic Examples](#basic-examples)
2. [SQL Query Building](#sql-query-building)
3. [Configuration Strings](#configuration-strings)
4. [Template Systems](#template-systems)
5. [Advanced Patterns](#advanced-patterns)

---

## Basic Examples

### Simple String Building

```php
use Concept\Expression\Expression;

$expr = new Expression();
$expr->push('Hello', 'World');

echo $expr; // Output: Hello World
```

### With Custom Separator

```php
$expr = (new Expression())
    ->push('apple', 'banana', 'cherry')
    ->join(', ');

echo $expr; // Output: apple, banana, cherry
```

### Wrapping Values

```php
$expr = (new Expression())
    ->push('important')
    ->wrap('[', ']');

echo $expr; // Output: [important]
```

### Wrapping Items

```php
$expr = (new Expression())
    ->push('a', 'b', 'c')
    ->wrapItem('"')
    ->join(', ');

echo $expr; // Output: "a", "b", "c"
```

---

## SQL Query Building

### Simple SELECT Query

```php
$columns = (new Expression())
    ->push('id', 'name', 'email')
    ->wrapItem('`')
    ->join(', ');

$query = (new Expression())
    ->push('SELECT', $columns, 'FROM', 'users');

echo $query;
// Output: SELECT `id`, `name`, `email` FROM users
```

### SELECT with WHERE Clause

```php
$columns = (new Expression())
    ->push('id', 'name')
    ->wrapItem('`')
    ->join(', ');

$conditions = (new Expression())
    ->push('status = "active"', 'age > 18')
    ->join(' AND ')
    ->wrap('WHERE ', '');

$query = (new Expression())
    ->push('SELECT', $columns)
    ->push('FROM', 'users')
    ->push($conditions);

echo $query;
// Output: SELECT `id`, `name` FROM users WHERE status = "active" AND age > 18
```

### INSERT Query

```php
$columns = (new Expression())
    ->push('name', 'email', 'status')
    ->wrapItem('`')
    ->join(', ')
    ->wrap('(', ')');

$values = (new Expression())
    ->push("'John Doe'", "'john@example.com'", "'active'")
    ->join(', ')
    ->wrap('(', ')');

$query = (new Expression())
    ->push('INSERT INTO', 'users', $columns)
    ->push('VALUES', $values);

echo $query;
// Output: INSERT INTO users (`name`, `email`, `status`) VALUES ('John Doe', 'john@example.com', 'active')
```

### UPDATE Query

```php
$sets = (new Expression())
    ->push('name = "Jane Doe"', 'status = "inactive"')
    ->join(', ')
    ->wrap('SET ', '');

$where = (new Expression())
    ->push('id = 1')
    ->wrap('WHERE ', '');

$query = (new Expression())
    ->push('UPDATE', 'users')
    ->push($sets)
    ->push($where);

echo $query;
// Output: UPDATE users SET name = "Jane Doe", status = "inactive" WHERE id = 1
```

### Complex JOIN Query

```php
$columns = (new Expression())
    ->push('u.id', 'u.name', 'p.title', 'p.created_at')
    ->wrapItem('`')
    ->join(', ');

$joins = (new Expression())
    ->push('LEFT JOIN', 'posts', 'p', 'ON', 'u.id = p.user_id');

$where = (new Expression())
    ->push('u.status = "active"', 'p.published = 1')
    ->join(' AND ')
    ->wrap('WHERE ', '');

$query = (new Expression())
    ->push('SELECT', $columns)
    ->push('FROM', 'users', 'u')
    ->push($joins)
    ->push($where);

echo $query;
// Output: SELECT `u.id`, `u.name`, `p.title`, `p.created_at` FROM users u LEFT JOIN posts p ON u.id = p.user_id WHERE u.status = "active" AND p.published = 1
```

### Dynamic Query Builder

```php
function buildSelectQuery(array $columns, string $table, array $conditions = []): string
{
    $columnsExpr = (new Expression())
        ->push(...$columns)
        ->wrapItem('`')
        ->join(', ');

    $query = (new Expression())
        ->push('SELECT', $columnsExpr, 'FROM', $table);

    if (!empty($conditions)) {
        $whereExpr = (new Expression())
            ->push(...$conditions)
            ->join(' AND ')
            ->wrap('WHERE ', '');
        $query->push($whereExpr);
    }

    return (string)$query;
}

// Usage
echo buildSelectQuery(['id', 'name'], 'users', ['status = "active"', 'age > 18']);
// Output: SELECT `id`, `name` FROM users WHERE status = "active" AND age > 18
```

---

## Configuration Strings

### Environment Variables

```php
$config = (new Expression())
    ->push('DB_HOST=localhost', 'DB_PORT=3306', 'DB_NAME=myapp')
    ->decorateItem(fn($item) => strtoupper($item))
    ->join("\n");

echo $config;
// Output:
// DB_HOST=LOCALHOST
// DB_PORT=3306
// DB_NAME=MYAPP
```

### Command Line Arguments

```php
$args = (new Expression())
    ->push('verbose', 'debug', 'force')
    ->decorateItem(fn($item) => "--$item")
    ->join(' ');

$command = (new Expression())
    ->push('php', 'script.php', $args);

echo $command;
// Output: php script.php --verbose --debug --force
```

### CSS Classes

```php
$classes = (new Expression())
    ->push('btn', 'btn-primary', 'btn-lg', 'active')
    ->join(' ');

echo '<button class="' . $classes . '">Click Me</button>';
// Output: <button class="btn btn-primary btn-lg active">Click Me</button>
```

---

## Template Systems

### Simple Template

```php
$template = (new Expression())
    ->push('Hello', '{name}!', 'Welcome to', '{site}');

$rendered = $template->withContext([
    'name' => 'John',
    'site' => 'MyApp'
]);

echo $rendered;
// Output: Hello John! Welcome to MyApp
```

### Email Template

```php
$email = (new Expression())
    ->push(
        'Dear {name},',
        '',
        'Your order #{order_id} has been {status}.',
        '',
        'Thank you for shopping with us!',
        '',
        'Best regards,',
        '{company_name}'
    )
    ->join("\n");

$rendered = $email->withContext([
    'name' => 'Jane Doe',
    'order_id' => '12345',
    'status' => 'shipped',
    'company_name' => 'ACME Corp'
]);

echo $rendered;
```

### Reusable Templates

```php
class EmailTemplate
{
    private Expression $header;
    private Expression $footer;

    public function __construct()
    {
        $this->header = (new Expression())
            ->push('Dear {name},', '')
            ->join("\n");

        $this->footer = (new Expression())
            ->push('', 'Best regards,', '{company_name}')
            ->join("\n");
    }

    public function build(string $body, array $context): string
    {
        $email = (new Expression())
            ->push($this->header, $body, $this->footer)
            ->join("\n");

        return (string)$email->withContext($context);
    }
}

// Usage
$template = new EmailTemplate();
echo $template->build(
    'Your order has been shipped!',
    ['name' => 'John', 'company_name' => 'ACME']
);
```

---

## Advanced Patterns

### Builder Pattern

```php
class QueryBuilder
{
    private Expression $query;

    public function __construct()
    {
        $this->query = new Expression();
    }

    public function select(string ...$columns): self
    {
        $columnsExpr = (new Expression())
            ->push(...$columns)
            ->wrapItem('`')
            ->join(', ');

        $this->query->push('SELECT', $columnsExpr);
        return $this;
    }

    public function from(string $table): self
    {
        $this->query->push('FROM', $table);
        return $this;
    }

    public function where(string ...$conditions): self
    {
        $whereExpr = (new Expression())
            ->push(...$conditions)
            ->join(' AND ')
            ->wrap('WHERE ', '');

        $this->query->push($whereExpr);
        return $this;
    }

    public function build(): string
    {
        return (string)$this->query;
    }

    public function reset(): self
    {
        $this->query->reset();
        return $this;
    }
}

// Usage
$builder = new QueryBuilder();
$sql = $builder
    ->select('id', 'name', 'email')
    ->from('users')
    ->where('status = "active"', 'age > 18')
    ->build();

echo $sql;
// Output: SELECT `id`, `name`, `email` FROM users WHERE status = "active" AND age > 18
```

### Factory Pattern

```php
class ExpressionFactory
{
    public static function sql(): Expression
    {
        return new Expression();
    }

    public static function columns(string ...$columns): Expression
    {
        return self::sql()
            ->push(...$columns)
            ->wrapItem('`')
            ->join(', ');
    }

    public static function values(string ...$values): Expression
    {
        return self::sql()
            ->push(...$values)
            ->join(', ')
            ->wrap('(', ')');
    }

    public static function conditions(string ...$conditions): Expression
    {
        return self::sql()
            ->push(...$conditions)
            ->join(' AND ');
    }
}

// Usage
$query = ExpressionFactory::sql()
    ->push('SELECT', ExpressionFactory::columns('id', 'name'))
    ->push('FROM', 'users')
    ->push('WHERE', ExpressionFactory::conditions('status = "active"'));

echo $query;
```

### Custom Decorators

```php
// SQL Comment Decorator
$commentDecorator = function(string $sql): string {
    return "/* Generated: " . date('Y-m-d H:i:s') . " */\n" . $sql;
};

// SQL Formatter Decorator
$formatDecorator = function(string $sql): string {
    return strtoupper($sql);
};

// Pretty Print Decorator
$prettyPrintDecorator = function(string $sql): string {
    $sql = str_replace(' FROM ', "\nFROM ", $sql);
    $sql = str_replace(' WHERE ', "\nWHERE ", $sql);
    return $sql;
};

$query = (new Expression())
    ->push('select', 'id', 'from', 'users', 'where', 'status = "active"')
    ->decorate($formatDecorator)
    ->decorate($prettyPrintDecorator)
    ->decorate($commentDecorator);

echo $query;
// Output:
// /* Generated: 2024-01-01 12:00:00 */
// SELECT ID
// FROM USERS
// WHERE STATUS = "ACTIVE"
```

### Conditional Building

```php
function buildDynamicQuery(
    array $columns,
    string $table,
    ?array $where = null,
    ?string $orderBy = null,
    ?int $limit = null
): string {
    $columnsExpr = (new Expression())
        ->push(...$columns)
        ->wrapItem('`')
        ->join(', ');

    $query = (new Expression())
        ->push('SELECT', $columnsExpr, 'FROM', $table);

    if ($where) {
        $whereExpr = (new Expression())
            ->push(...$where)
            ->join(' AND ')
            ->wrap('WHERE ', '');
        $query->push($whereExpr);
    }

    if ($orderBy) {
        $query->push('ORDER BY', $orderBy);
    }

    if ($limit) {
        $query->push('LIMIT', (string)$limit);
    }

    return (string)$query;
}

// Usage
echo buildDynamicQuery(
    ['id', 'name'],
    'users',
    ['status = "active"'],
    'created_at DESC',
    10
);
```

### Caching Pattern

```php
class CachedExpression
{
    private Expression $expression;
    private ?string $cached = null;

    public function __construct(Expression $expression)
    {
        $this->expression = $expression;
    }

    public function __toString(): string
    {
        if ($this->cached === null) {
            $this->cached = (string)$this->expression;
        }
        return $this->cached;
    }

    public function invalidate(): void
    {
        $this->cached = null;
    }

    public function getExpression(): Expression
    {
        return $this->expression;
    }
}

// Usage
$expr = (new Expression())
    ->push('SELECT', '*', 'FROM', 'users');

$cached = new CachedExpression($expr);

// First call - computes
echo $cached; // Renders and caches

// Subsequent calls - uses cache
echo $cached; // Returns cached value
echo $cached; // Returns cached value

// Modify underlying expression
$cached->getExpression()->push('WHERE', 'id = 1');
$cached->invalidate();

// Next call recomputes
echo $cached; // Renders and caches new value
```

### Prototype Pattern for Reusability

```php
// Create base query prototype
$baseQuery = (new Expression())
    ->push('SELECT')
    ->type('select');

// Create specific queries from prototype
$userQuery = $baseQuery->prototype()
    ->push('*', 'FROM', 'users');

$productQuery = $baseQuery->prototype()
    ->push('id, name, price', 'FROM', 'products');

$orderQuery = $baseQuery->prototype()
    ->push('order_id, total', 'FROM', 'orders')
    ->push('WHERE', 'status = "pending"');

echo $userQuery . "\n";    // SELECT * FROM users
echo $productQuery . "\n"; // SELECT id, name, price FROM products
echo $orderQuery . "\n";   // SELECT order_id, total FROM orders WHERE status = "pending"
```

---

## Real-World Use Case: Query Builder Library

Here's a complete example of building a simple query builder library:

```php
namespace App\QueryBuilder;

use Concept\Expression\Expression;

class Query
{
    private Expression $expr;
    private bool $distinct = false;

    public function __construct()
    {
        $this->expr = new Expression();
    }

    public function select(string ...$columns): self
    {
        $this->expr->push($this->distinct ? 'SELECT DISTINCT' : 'SELECT');
        
        if (empty($columns)) {
            $this->expr->push('*');
        } else {
            $columnsExpr = (new Expression())
                ->push(...$columns)
                ->join(', ');
            $this->expr->push($columnsExpr);
        }
        
        return $this;
    }

    public function distinct(): self
    {
        $this->distinct = true;
        return $this;
    }

    public function from(string $table, ?string $alias = null): self
    {
        $this->expr->push('FROM', $table);
        if ($alias) {
            $this->expr->push($alias);
        }
        return $this;
    }

    public function join(string $table, string $on, string $type = 'INNER'): self
    {
        $this->expr->push($type, 'JOIN', $table, 'ON', $on);
        return $this;
    }

    public function where(string ...$conditions): self
    {
        if (!empty($conditions)) {
            $whereExpr = (new Expression())
                ->push(...$conditions)
                ->join(' AND ')
                ->wrap('WHERE ', '');
            $this->expr->push($whereExpr);
        }
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->expr->push('ORDER BY', $column, $direction);
        return $this;
    }

    public function limit(int $limit, ?int $offset = null): self
    {
        $this->expr->push('LIMIT', (string)$limit);
        if ($offset !== null) {
            $this->expr->push('OFFSET', (string)$offset);
        }
        return $this;
    }

    public function toSql(): string
    {
        return (string)$this->expr;
    }

    public function __toString(): string
    {
        return $this->toSql();
    }
}

// Usage
$query = (new Query())
    ->distinct()
    ->select('u.id', 'u.name', 'p.title')
    ->from('users', 'u')
    ->join('posts p', 'u.id = p.user_id', 'LEFT')
    ->where('u.status = "active"', 'p.published = 1')
    ->orderBy('u.created_at', 'DESC')
    ->limit(10, 20);

echo $query->toSql();
```

This query builder demonstrates how the Expression library can be used to build practical, real-world tools.
