# Advanced Examples - Showcasing Expression Power

This document showcases the true power and flexibility of the Expression library through diverse, real-world examples beyond SQL queries.

## Table of Contents

1. [Syntax Sugar Deep Dive](#syntax-sugar-deep-dive)
2. [JSON & API Builders](#json--api-builders)
3. [HTML & XML Generation](#html--xml-generation)
4. [CLI Command Builders](#cli-command-builders)
5. [Code Generation](#code-generation)
6. [Configuration File Generators](#configuration-file-generators)
7. [Test Data Builders](#test-data-builders)
8. [Domain-Specific Languages (DSL)](#domain-specific-languages-dsl)
9. [Creative Use Cases](#creative-use-cases)

---

## Syntax Sugar Deep Dive

### The Power of `__invoke()` - Callable Expressions

The `__invoke()` magic method makes expressions callable, enabling ultra-concise syntax:

```php
use Concept\Expression\Expression;

// Traditional approach
$expr = new Expression();
$expr->push('Hello')->push('World');

// Callable syntax - Much more elegant!
$expr = new Expression();
$expr('Hello')('World');

// Even better - chain it all together
$greeting = (new Expression())
    ('Hello')
    ('Beautiful')
    ('World');

echo $greeting; // Output: Hello Beautiful World
```

### Fluent Chaining - The Art of Expression Building

```php
// Build complex expressions in a single, readable chain
$styledText = (new Expression())
    ('bold', 'important', 'highlight')
    ->wrapItem('[', ']')          // Wrap each word
    ->join(' • ')                  // Join with bullet
    ->decorate(fn($s) => "⭐ $s ⭐"); // Add stars

echo $styledText; 
// Output: ⭐ [bold] • [important] • [highlight] ⭐
```

### Decorator Stacking - Layered Transformations

```php
// Stack multiple decorators for progressive transformation
$pipeline = (new Expression())
    ('apple', 'banana', 'cherry')
    ->decorateItem(fn($item) => ucfirst($item))           // Step 1: Capitalize
    ->decorateItem(fn($item) => "🍎 $item")               // Step 2: Add emoji
    ->decorateItem(fn($item) => "[$item]")                // Step 3: Wrap in brackets
    ->join(' -> ')                                        // Step 4: Join with arrows
    ->decorate(fn($str) => "Fruit Pipeline: $str")       // Step 5: Add header
    ->decorate(fn($str) => "┌─────────────┐\n│ $str │\n└─────────────┘"); // Step 6: Box it

echo $pipeline;
// Output:
// ┌─────────────┐
// │ Fruit Pipeline: [🍎 Apple] -> [🍎 Banana] -> [🍎 Cherry] │
// └─────────────┘
```

### Context Interpolation - Template Magic

```php
// Create reusable templates with context
$emailTemplate = (new Expression())
    ('Dear {name},', '', 'Your {item} order #{order_id} is {status}.', '')
    ->join("\n");

// Generate multiple emails from the same template
$email1 = $emailTemplate->withContext([
    'name' => 'Alice',
    'item' => 'laptop',
    'order_id' => '12345',
    'status' => 'shipped'
]);

$email2 = $emailTemplate->withContext([
    'name' => 'Bob',
    'item' => 'phone',
    'order_id' => '67890',
    'status' => 'processing'
]);

// Original template remains unchanged!
echo $emailTemplate; // Still has {name}, {item}, etc.
```

---

## JSON & API Builders

### REST API Response Builder

```php
class JsonResponseBuilder
{
    private Expression $response;

    public function __construct()
    {
        $this->response = new Expression();
    }

    public function success(array $data, string $message = 'Success'): self
    {
        $json = (new Expression())
            ('{"status":"success"')
            ('"message":"' . $message . '"')
            ('"data":' . json_encode($data))
            ('"timestamp":"' . date('c') . '"}')
            ->join(',');

        $this->response->push($json);
        return $this;
    }

    public function error(string $message, int $code = 400): self
    {
        $json = (new Expression())
            ('{"status":"error"')
            ('"message":"' . $message . '"')
            ('"code":' . $code)
            ('"timestamp":"' . date('c') . '"}')
            ->join(',');

        $this->response->push($json);
        return $this;
    }

    public function build(): string
    {
        return (string)$this->response;
    }
}

// Usage
$response = new JsonResponseBuilder();
echo $response->success(['user_id' => 123, 'username' => 'alice'], 'Login successful')->build();
```

### GraphQL Query Builder

```php
// Build complex GraphQL queries programmatically
$fields = (new Expression())
    ('id', 'name', 'email', 'avatar')
    ->join("\n    ");

$userQuery = (new Expression())
    ('query {')
    ('  user(id: "123") {')
    ("    $fields")
    ('  }')
    ('}')
    ->join("\n");

echo $userQuery;
// Output:
// query {
//   user(id: "123") {
//     id
//     name
//     email
//     avatar
//   }
// }
```

---

## HTML & XML Generation

### HTML Component Builder

```php
class HtmlBuilder
{
    public static function tag(string $name, string $content, array $attrs = []): Expression
    {
        $attrStr = '';
        if (!empty($attrs)) {
            $attrExpr = (new Expression());
            foreach ($attrs as $key => $value) {
                $attrExpr->push("$key=\"$value\"");
            }
            $attrStr = ' ' . $attrExpr->join(' ');
        }

        return (new Expression())
            ("<$name$attrStr>")
            ($content)
            ("</$name>");
    }

    public static function list(array $items, array $attrs = []): Expression
    {
        $listItems = (new Expression());
        foreach ($items as $item) {
            $li = self::tag('li', $item);
            $listItems->push($li);
        }

        return self::tag('ul', (string)$listItems->join("\n  "), $attrs);
    }

    public static function card(string $title, string $content, string $footer = ''): Expression
    {
        $cardBody = (new Expression())
            (self::tag('h2', $title, ['class' => 'card-title']))
            (self::tag('p', $content, ['class' => 'card-text']))
            ->join("\n    ");

        $card = (new Expression())
            ('<div class="card">')
            ("  $cardBody");

        if ($footer) {
            $card->push('  ' . self::tag('footer', $footer, ['class' => 'card-footer']));
        }

        $card->push('</div>');
        return $card->join("\n");
    }
}

// Usage
echo HtmlBuilder::card(
    'Welcome!',
    'This is a beautiful card built with Expression library.',
    'Created with Expression'
);
```

### XML Document Builder

```php
class XmlBuilder
{
    private Expression $xml;

    public function __construct(string $rootElement)
    {
        $this->xml = (new Expression())
            ('<?xml version="1.0" encoding="UTF-8"?>')
            ("<$rootElement>");
    }

    public function element(string $name, $content, array $attrs = []): self
    {
        $attrStr = '';
        if (!empty($attrs)) {
            $attrParts = [];
            foreach ($attrs as $key => $value) {
                $attrParts[] = "$key=\"$value\"";
            }
            $attrStr = ' ' . implode(' ', $attrParts);
        }

        $elem = (new Expression())
            ("  <$name$attrStr>$content</$name>");

        $this->xml->push($elem);
        return $this;
    }

    public function build(string $rootElement): string
    {
        $this->xml->push("</$rootElement>");
        return (string)$this->xml->join("\n");
    }
}

// Usage
$rss = new XmlBuilder('rss');
$feed = $rss
    ->element('title', 'My Blog', ['lang' => 'en'])
    ->element('description', 'A blog about expressions')
    ->element('link', 'https://example.com')
    ->build('rss');

echo $feed;
```

---

## CLI Command Builders

### Docker Command Composer

```php
class DockerCommand
{
    private Expression $cmd;

    public function __construct(string $command = 'docker')
    {
        $this->cmd = (new Expression())($command);
    }

    public function run(string $image): self
    {
        $this->cmd('run');
        $this->cmd($image);
        return $this;
    }

    public function interactive(): self
    {
        $this->cmd('-it');
        return $this;
    }

    public function detach(): self
    {
        $this->cmd('-d');
        return $this;
    }

    public function name(string $name): self
    {
        $this->cmd("--name $name");
        return $this;
    }

    public function port(int $host, int $container): self
    {
        $this->cmd("-p $host:$container");
        return $this;
    }

    public function volume(string $host, string $container): self
    {
        $this->cmd("-v $host:$container");
        return $this;
    }

    public function env(string $key, string $value): self
    {
        $this->cmd("-e $key=$value");
        return $this;
    }

    public function build(): string
    {
        return (string)$this->cmd;
    }
}

// Usage - Build complex Docker commands fluently
$command = (new DockerCommand())
    ->run('nginx:latest')
    ->detach()
    ->name('my-nginx')
    ->port(8080, 80)
    ->port(8443, 443)
    ->volume('/host/nginx/conf', '/etc/nginx')
    ->env('NGINX_HOST', 'example.com')
    ->env('NGINX_PORT', '80')
    ->build();

echo $command;
// Output: docker run -d --name my-nginx -p 8080:80 -p 8443:443 -v /host/nginx/conf:/etc/nginx -e NGINX_HOST=example.com -e NGINX_PORT=80 nginx:latest
```

### Git Command Builder

```php
class GitCommand
{
    public static function commit(string $message, array $files = []): Expression
    {
        $cmd = (new Expression())('git');

        if (!empty($files)) {
            $fileExpr = (new Expression())('add')(...$files);
            $cmd->push($fileExpr);
            $cmd('&&')('git');
        }

        $cmd('commit')('-m')->push("\"$message\"");
        return $cmd;
    }

    public static function branch(string $name, bool $checkout = true): Expression
    {
        $cmd = (new Expression())('git')('branch')($name);
        
        if ($checkout) {
            $cmd('&&')('git')('checkout')($name);
        }
        
        return $cmd;
    }

    public static function push(string $remote = 'origin', string $branch = 'main'): Expression
    {
        return (new Expression())
            ('git')
            ('push')
            ($remote)
            ($branch);
    }
}

// Usage
echo GitCommand::commit('Add new feature', ['src/Feature.php', 'tests/FeatureTest.php']);
// Output: git add src/Feature.php tests/FeatureTest.php && git commit -m "Add new feature"

echo GitCommand::branch('feature/awesome', true);
// Output: git branch feature/awesome && git checkout feature/awesome
```

---

## Code Generation

### PHP Class Generator

```php
class PhpClassGenerator
{
    private string $namespace;
    private string $className;
    private array $properties = [];
    private array $methods = [];

    public function __construct(string $namespace, string $className)
    {
        $this->namespace = $namespace;
        $this->className = $className;
    }

    public function addProperty(string $name, string $type, $default = null): self
    {
        $this->properties[] = ['name' => $name, 'type' => $type, 'default' => $default];
        return $this;
    }

    public function addMethod(string $name, string $returnType, string $body): self
    {
        $this->methods[] = ['name' => $name, 'returnType' => $returnType, 'body' => $body];
        return $this;
    }

    public function generate(): string
    {
        $class = (new Expression())
            ('<?php')
            ("namespace $this->namespace;")
            ('')
            ("class $this->className")
            ('{');

        // Add properties
        foreach ($this->properties as $prop) {
            $default = $prop['default'] !== null ? " = {$prop['default']}" : '';
            $class->push("    private {$prop['type']} \${$prop['name']}$default;");
        }

        if (!empty($this->properties)) {
            $class->push('');
        }

        // Add methods
        foreach ($this->methods as $method) {
            $class
                ->push("    public function {$method['name']}(): {$method['returnType']}")
                ->push('    {')
                ->push("        {$method['body']}")
                ->push('    }')
                ->push('');
        }

        $class->push('}');

        return (string)$class->join("\n");
    }
}

// Usage
$generator = new PhpClassGenerator('App\\Models', 'User');
$generator
    ->addProperty('id', 'int')
    ->addProperty('name', 'string')
    ->addProperty('email', 'string')
    ->addMethod('getId', 'int', 'return $this->id;')
    ->addMethod('getName', 'string', 'return $this->name;');

echo $generator->generate();
```

### Markdown Table Generator

```php
class MarkdownTableBuilder
{
    private array $headers;
    private array $rows = [];
    private array $alignments = [];

    public function __construct(array $headers, array $alignments = [])
    {
        $this->headers = $headers;
        $this->alignments = $alignments ?: array_fill(0, count($headers), 'left');
    }

    public function addRow(array $row): self
    {
        $this->rows[] = $row;
        return $this;
    }

    public function build(): string
    {
        // Header row
        $header = (new Expression())
            (...$this->headers)
            ->wrapItem('| ', ' ')
            ->join('')
            ->wrap('', '|');

        // Separator row
        $separators = [];
        foreach ($this->alignments as $align) {
            $separators[] = match($align) {
                'left' => ':---',
                'center' => ':---:',
                'right' => '---:',
                default => '---'
            };
        }

        $separator = (new Expression())
            (...$separators)
            ->wrapItem('| ', ' ')
            ->join('')
            ->wrap('', '|');

        // Build table
        $table = (new Expression())
            ($header)
            ($separator);

        // Add data rows
        foreach ($this->rows as $row) {
            $rowExpr = (new Expression())
                (...$row)
                ->wrapItem('| ', ' ')
                ->join('')
                ->wrap('', '|');
            $table->push($rowExpr);
        }

        return (string)$table->join("\n");
    }
}

// Usage
$table = new MarkdownTableBuilder(
    ['Name', 'Age', 'City'],
    ['left', 'center', 'right']
);

$table
    ->addRow(['Alice', '30', 'New York'])
    ->addRow(['Bob', '25', 'London'])
    ->addRow(['Charlie', '35', 'Tokyo']);

echo $table->build();
// Output:
// | Name | Age | City |
// | :--- | :---: | ---: |
// | Alice | 30 | New York |
// | Bob | 25 | London |
// | Charlie | 35 | Tokyo |
```

---

## Configuration File Generators

### ENV File Builder

```php
class EnvFileBuilder
{
    private Expression $env;

    public function __construct()
    {
        $this->env = new Expression();
    }

    public function section(string $name): self
    {
        $this->env
            ->push('')
            ->push("# $name")
            ->push(str_repeat('-', strlen($name) + 2));
        return $this;
    }

    public function set(string $key, $value, string $comment = ''): self
    {
        $value = is_bool($value) ? ($value ? 'true' : 'false') : $value;
        $line = "$key=$value";
        if ($comment) {
            $line .= " # $comment";
        }
        $this->env->push($line);
        return $this;
    }

    public function build(): string
    {
        return (string)$this->env->join("\n");
    }
}

// Usage
$env = new EnvFileBuilder();
$config = $env
    ->section('Database Configuration')
    ->set('DB_HOST', 'localhost', 'Database server host')
    ->set('DB_PORT', '5432', 'PostgreSQL port')
    ->set('DB_NAME', 'myapp', 'Database name')
    ->set('DB_USER', 'admin')
    ->set('DB_PASSWORD', 'secret123', 'Use strong passwords!')
    
    ->section('Application Settings')
    ->set('APP_ENV', 'production')
    ->set('APP_DEBUG', false, 'Disable in production')
    ->set('APP_URL', 'https://example.com')
    
    ->section('API Keys')
    ->set('STRIPE_KEY', 'sk_live_xxxxx', 'Payment processing')
    ->set('SENDGRID_KEY', 'SG.xxxxx', 'Email service')
    
    ->build();

echo $config;
```

### YAML Configuration Builder

```php
class YamlBuilder
{
    private Expression $yaml;
    private int $indent = 0;

    public function __construct()
    {
        $this->yaml = new Expression();
    }

    public function key(string $key, $value = null): self
    {
        $spaces = str_repeat('  ', $this->indent);
        if ($value === null) {
            $this->yaml->push("$spaces$key:");
            $this->indent++;
        } else {
            $formattedValue = is_bool($value) ? ($value ? 'true' : 'false') : $value;
            $this->yaml->push("$spaces$key: $formattedValue");
        }
        return $this;
    }

    public function arrayItem($value): self
    {
        $spaces = str_repeat('  ', $this->indent);
        $this->yaml->push("$spaces- $value");
        return $this;
    }

    public function end(): self
    {
        $this->indent = max(0, $this->indent - 1);
        return $this;
    }

    public function build(): string
    {
        return (string)$this->yaml->join("\n");
    }
}

// Usage
$yaml = new YamlBuilder();
$config = $yaml
    ->key('version', '3.8')
    ->key('services')
    ->key('web')
    ->key('image', 'nginx:latest')
    ->key('ports')
    ->arrayItem('80:80')
    ->arrayItem('443:443')
    ->end()
    ->key('environment')
    ->arrayItem('NGINX_HOST=example.com')
    ->arrayItem('NGINX_PORT=80')
    ->end()
    ->end()
    ->build();

echo $config;
```

---

## Test Data Builders

### Fake Data Generator

```php
class TestDataBuilder
{
    public static function user(array $overrides = []): Expression
    {
        $defaults = [
            'id' => rand(1, 1000),
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'user',
            'active' => true
        ];

        $data = array_merge($defaults, $overrides);
        
        $fields = (new Expression());
        foreach ($data as $key => $value) {
            $val = is_bool($value) ? ($value ? 'true' : 'false') : "\"$value\"";
            $fields->push("\"$key\": $val");
        }

        return $fields
            ->join(', ')
            ->wrap('{', '}');
    }

    public static function users(int $count, array $overrides = []): Expression
    {
        $users = new Expression();
        for ($i = 0; $i < $count; $i++) {
            $users->push(self::user(array_merge($overrides, ['id' => $i + 1])));
        }
        
        return $users
            ->join(",\n  ")
            ->wrap("[\n  ", "\n]");
    }
}

// Usage
echo TestDataBuilder::user(['name' => 'Alice', 'role' => 'admin']);
// Output: {"id": "123", "name": "Alice", "email": "john@example.com", "role": "admin", "active": true}

echo TestDataBuilder::users(3, ['role' => 'tester']);
// Generates an array of 3 user objects
```

---

## Domain-Specific Languages (DSL)

### Route Definition DSL

```php
class RouteDsl
{
    private Expression $routes;

    public function __construct()
    {
        $this->routes = new Expression();
    }

    public function get(string $path, string $handler): self
    {
        $route = (new Expression())
            ("Route::get('$path',")
            ("  $handler");
        
        $this->routes->push($route->wrap('', ');'));
        return $this;
    }

    public function post(string $path, string $handler): self
    {
        $route = (new Expression())
            ("Route::post('$path',")
            ("  $handler");
        
        $this->routes->push($route->wrap('', ');'));
        return $this;
    }

    public function group(string $prefix, callable $callback): self
    {
        $this->routes->push("Route::prefix('$prefix')->group(function () {");
        $callback($this);
        $this->routes->push('});');
        return $this;
    }

    public function build(): string
    {
        return (string)$this->routes->join("\n");
    }
}

// Usage
$routes = new RouteDsl();
$config = $routes
    ->get('/', 'HomeController@index')
    ->get('/about', 'HomeController@about')
    ->get('/users', 'UserController@index')
    ->post('/users', 'UserController@store')
    ->build();

echo $config;
```

---

## Creative Use Cases

### ASCII Art Banner Generator

```php
class AsciiBanner
{
    public static function create(string $text, string $style = 'simple'): Expression
    {
        $banner = new Expression();
        
        $width = strlen($text) + 4;
        $border = str_repeat('=', $width);
        
        return $banner
            ($border)
            ("| $text |")
            ($border)
            ->join("\n");
    }

    public static function box(string $text, string $char = '*'): Expression
    {
        $width = strlen($text) + 2;
        $border = str_repeat($char, $width + 2);
        
        return (new Expression())
            ($border)
            ("$char $text $char")
            ($border)
            ->join("\n");
    }

    public static function fancy(string $title, string $subtitle = ''): Expression
    {
        $width = max(strlen($title), strlen($subtitle)) + 4;
        $border = '╔' . str_repeat('═', $width) . '╗';
        $bottom = '╚' . str_repeat('═', $width) . '╝';
        
        $banner = (new Expression())
            ($border)
            ('║ ' . str_pad($title, $width - 2) . ' ║');
        
        if ($subtitle) {
            $banner->push('║ ' . str_pad($subtitle, $width - 2) . ' ║');
        }
        
        $banner->push($bottom);
        
        return $banner->join("\n");
    }
}

// Usage
echo AsciiBanner::fancy('Expression Library', 'Powerful PHP Expressions');
// Output:
// ╔═══════════════════════════╗
// ║ Expression Library       ║
// ║ Powerful PHP Expressions ║
// ╚═══════════════════════════╝
```

### Log Message Formatter

```php
class LogFormatter
{
    public static function error(string $message, array $context = []): Expression
    {
        return self::format('ERROR', $message, $context, '❌');
    }

    public static function warning(string $message, array $context = []): Expression
    {
        return self::format('WARN', $message, $context, '⚠️');
    }

    public static function info(string $message, array $context = []): Expression
    {
        return self::format('INFO', $message, $context, 'ℹ️');
    }

    public static function success(string $message, array $context = []): Expression
    {
        return self::format('SUCCESS', $message, $context, '✅');
    }

    private static function format(string $level, string $message, array $context, string $icon): Expression
    {
        $log = (new Expression())
            (date('[Y-m-d H:i:s]'))
            ("$icon [$level]")
            ($message);

        if (!empty($context)) {
            $contextStr = json_encode($context, JSON_PRETTY_PRINT);
            $log->push("Context: $contextStr");
        }

        return $log;
    }
}

// Usage
echo LogFormatter::error('Database connection failed', ['host' => 'localhost', 'port' => 5432]);
echo "\n";
echo LogFormatter::success('User registration completed', ['user_id' => 123]);
```

### Progress Bar Generator

```php
class ProgressBar
{
    public static function generate(int $current, int $total, int $width = 50): Expression
    {
        $percentage = ($current / $total) * 100;
        $filled = (int)(($current / $total) * $width);
        $empty = $width - $filled;

        $bar = (new Expression())
            ('[')
            (str_repeat('█', $filled))
            (str_repeat('░', $empty))
            (']')
            (sprintf(' %d/%d (%.1f%%)', $current, $total, $percentage));

        return $bar;
    }

    public static function multiStep(array $steps): Expression
    {
        $progress = new Expression();
        
        foreach ($steps as $i => $step) {
            $status = $step['completed'] ? '✓' : ($step['current'] ? '▶' : '○');
            $stepExpr = (new Expression())
                ("$status Step " . ($i + 1) . ": " . $step['name']);
            
            $progress->push($stepExpr);
        }

        return $progress->join("\n");
    }
}

// Usage
echo ProgressBar::generate(75, 100);
// Output: [█████████████████████████████████████░░░░░░░░░░░░░░░] 75/100 (75.0%)

echo "\n\n";

echo ProgressBar::multiStep([
    ['name' => 'Download', 'completed' => true, 'current' => false],
    ['name' => 'Extract', 'completed' => true, 'current' => false],
    ['name' => 'Install', 'completed' => false, 'current' => true],
    ['name' => 'Configure', 'completed' => false, 'current' => false],
]);
```

---

## Conclusion

These examples demonstrate that the Expression library is far more than a query builder. It's a powerful, flexible tool for:

- **Building any text-based output** with elegant, readable code
- **Creating DSLs** that feel native to PHP
- **Composing complex structures** from simple pieces
- **Maintaining clean code** through fluent interfaces and decorator patterns
- **Reusing templates** through context interpolation
- **Progressive transformation** through decorator stacking

The true power lies in its **composability**, **fluent syntax**, and **decorator pattern** - making it perfect for any scenario where you need to build structured text programmatically.
