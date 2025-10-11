# Contributing to Expression

Thank you for your interest in contributing to the Expression library! This document provides guidelines and instructions for contributing.

## Table of Contents

1. [Code of Conduct](#code-of-conduct)
2. [Getting Started](#getting-started)
3. [Development Setup](#development-setup)
4. [Making Changes](#making-changes)
5. [Testing](#testing)
6. [Coding Standards](#coding-standards)
7. [Submitting Changes](#submitting-changes)
8. [Reporting Issues](#reporting-issues)

## Code of Conduct

### Our Pledge

We are committed to providing a welcoming and inclusive environment for all contributors, regardless of experience level, gender, gender identity and expression, sexual orientation, disability, personal appearance, body size, race, ethnicity, age, religion, or nationality.

### Our Standards

**Positive behaviors include:**
- Being respectful and inclusive
- Welcoming newcomers
- Accepting constructive criticism gracefully
- Focusing on what's best for the community
- Showing empathy towards others

**Unacceptable behaviors include:**
- Harassment or discriminatory language
- Personal or political attacks
- Publishing others' private information
- Any conduct that could be considered inappropriate in a professional setting

## Getting Started

### Prerequisites

- PHP 8.2 or higher
- Composer
- Git

### Fork and Clone

1. Fork the repository on GitHub
2. Clone your fork locally:

```bash
git clone https://github.com/YOUR_USERNAME/expression.git
cd expression
```

3. Add the upstream repository:

```bash
git remote add upstream https://github.com/Concept-Labs/expression.git
```

## Development Setup

### Install Dependencies

```bash
composer install
```

This will install:
- PHPUnit (testing framework)
- Pest (BDD testing framework)
- Development dependencies

### Verify Installation

Run the test suite to ensure everything is working:

```bash
# Run Pest tests
composer test

# Run PHPUnit tests
composer test:phpunit
```

All tests should pass on a fresh installation.

## Making Changes

### Creating a Branch

Always create a new branch for your changes:

```bash
git checkout -b feature/your-feature-name
# or
git checkout -b fix/your-bug-fix
```

**Branch naming conventions:**
- `feature/` - New features
- `fix/` - Bug fixes
- `docs/` - Documentation changes
- `refactor/` - Code refactoring
- `test/` - Test additions or modifications

### Making Your Changes

1. **Keep changes focused** - One feature or fix per pull request
2. **Write tests** - All new code should have corresponding tests
3. **Update documentation** - Update relevant documentation
4. **Follow coding standards** - See [Coding Standards](#coding-standards)

### Code Organization

```
expression/
├── src/                    # Source code
│   ├── Expression.php      # Main expression class
│   ├── ExpressionInterface.php
│   ├── Decorator/          # Decorator-related classes
│   └── Exception/          # Exception classes
├── tests/                  # Test files
│   ├── Unit/              # Unit tests
│   └── Helpers.php        # Test helpers
├── docs/                   # Documentation
└── composer.json          # Dependencies and scripts
```

## Testing

### Running Tests

```bash
# Run all Pest tests
composer test

# Run PHPUnit tests
composer test:phpunit

# Run with coverage (requires Xdebug)
composer test:coverage

# Run specific test file
vendor/bin/pest tests/Unit/ExpressionTest.php

# Run specific test by name
vendor/bin/pest --filter "can push scalar values"
```

### Writing Tests

We use both Pest and PHPUnit for testing. You can write tests in either framework.

#### Pest Example

```php
use Concept\Expression\Expression;

it('can push scalar values', function () {
    $expression = new Expression();
    $expression->push('SELECT', 'column');
    
    expect($expression->isEmpty())->toBeFalse();
    expect((string)$expression)->toBe('SELECT column');
});
```

#### PHPUnit Example

```php
namespace Concept\Expression\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Concept\Expression\Expression;

class ExpressionTest extends TestCase
{
    public function testCanPushScalarValues(): void
    {
        $expression = new Expression();
        $expression->push('SELECT', 'column');
        
        $this->assertFalse($expression->isEmpty());
        $this->assertEquals('SELECT column', (string)$expression);
    }
}
```

### Test Guidelines

1. **Test one thing** - Each test should verify one specific behavior
2. **Use descriptive names** - Test names should clearly describe what they test
3. **Arrange-Act-Assert** - Structure tests clearly
4. **Test edge cases** - Don't just test the happy path
5. **Keep tests independent** - Tests should not depend on each other

### Test Coverage

We aim for high test coverage. Before submitting:

```bash
composer test:coverage
```

Aim for:
- At least 80% overall coverage
- 100% coverage for new code when possible

## Coding Standards

### PHP Standards

We follow PSR-12 coding standards with some additions:

#### Naming Conventions

- **Classes**: PascalCase (`Expression`, `DecoratorManager`)
- **Methods**: camelCase (`push()`, `decorateItem()`)
- **Properties**: camelCase (`$decoratorManager`, `$expressions`)
- **Constants**: UPPER_SNAKE_CASE (if added)

#### Type Hints

Always use type hints:

```php
// ✅ Good
public function push(...$expressions): static
{
    // ...
}

// ❌ Bad
public function push(...$expressions)
{
    // ...
}
```

#### Return Types

Always declare return types:

```php
// ✅ Good
public function isEmpty(): bool
{
    return empty($this->expressions);
}

// ❌ Bad
public function isEmpty()
{
    return empty($this->expressions);
}
```

#### Docblocks

Use docblocks for complex methods or where IDE hints are helpful:

```php
/**
 * Add expressions to the current expression.
 *
 * @param mixed ...$expressions
 * @return static
 * @throws InvalidArgumentException
 */
public function push(...$expressions): static
{
    // ...
}
```

#### Method Visibility

- Use `public` for API methods
- Use `protected` for methods intended for extension
- Use `private` for internal implementation

#### Fluent Interface

Methods that modify state should return `static`:

```php
public function push(...$expressions): static
{
    // modifications
    return $this;
}
```

### Code Style

#### Spacing

```php
// ✅ Good
if ($condition) {
    $this->doSomething();
}

// ❌ Bad
if($condition){
    $this->doSomething();
}
```

#### Line Length

- Keep lines under 120 characters when possible
- Break long method chains:

```php
// ✅ Good
$expr->push('SELECT', 'column')
     ->wrap('(', ')')
     ->decorate($decorator);

// Acceptable for short chains
$expr->push('a', 'b', 'c')->join(', ');
```

### Best Practices

1. **Avoid magic** - No magic methods unless absolutely necessary
2. **Fail fast** - Validate inputs early
3. **Immutability where appropriate** - Methods like `withContext()` return new instances
4. **Clear method names** - Names should describe what the method does
5. **Small methods** - Keep methods focused and small

## Submitting Changes

### Before Submitting

Ensure that:

1. **All tests pass**:
   ```bash
   composer test
   ```

2. **Code follows standards**:
   ```bash
   # If you have PHP CS Fixer installed
   vendor/bin/php-cs-fixer fix
   ```

3. **Documentation is updated** - Update relevant docs

4. **Commit messages are clear** - See [Commit Messages](#commit-messages)

### Commit Messages

Follow these guidelines:

#### Format

```
<type>: <subject>

<body>

<footer>
```

#### Types

- `feat:` - New feature
- `fix:` - Bug fix
- `docs:` - Documentation changes
- `test:` - Test additions or modifications
- `refactor:` - Code refactoring
- `style:` - Code style changes (formatting, etc.)
- `chore:` - Maintenance tasks

#### Examples

```
feat: Add support for custom join separators

Allows users to specify custom separators when joining expression items.
This provides more flexibility for building complex expressions.

Closes #123
```

```
fix: Correct DecoratorManager clone behavior

The clone method was not properly resetting decorators, causing
decorator pollution when cloning expressions.

Fixes #456
```

### Creating a Pull Request

1. **Push your branch** to your fork:
   ```bash
   git push origin feature/your-feature-name
   ```

2. **Create a pull request** on GitHub

3. **Fill in the PR template** with:
   - Description of changes
   - Related issue numbers
   - Testing performed
   - Breaking changes (if any)

4. **Request review** from maintainers

### PR Guidelines

- **One feature per PR** - Keep PRs focused
- **Update documentation** - Include doc changes in the same PR
- **Add tests** - New features need tests
- **Keep it small** - Smaller PRs are easier to review
- **Respond to feedback** - Address review comments promptly

## Reporting Issues

### Before Reporting

1. **Search existing issues** - Your issue may already be reported
2. **Try the latest version** - The issue may already be fixed
3. **Gather information** - Prepare reproduction steps

### Issue Template

When reporting a bug, include:

- **PHP version**: `php -v`
- **Library version**: Check `composer.json`
- **Description**: Clear description of the issue
- **Steps to reproduce**: Minimal code to reproduce
- **Expected behavior**: What should happen
- **Actual behavior**: What actually happens
- **Additional context**: Any other relevant information

### Example Issue

```markdown
**Bug Description**
Expression loses decorators when cloned

**PHP Version**
PHP 8.2.0

**Library Version**
1.0.0

**Steps to Reproduce**
\`\`\`php
$expr = new Expression();
$expr->push('value')->wrap('(', ')');
$clone = clone $expr;
echo $clone; // Expected: (value), Actual: value
\`\`\`

**Expected Behavior**
Cloned expression should maintain decorators

**Actual Behavior**
Decorators are lost on clone

**Additional Context**
This might be related to DecoratorManager cloning behavior
```

### Feature Requests

When requesting a feature:

1. **Describe the use case** - Why is this needed?
2. **Propose a solution** - How should it work?
3. **Consider alternatives** - What other approaches exist?
4. **Show examples** - Provide code examples

## Getting Help

### Communication Channels

- **GitHub Issues** - For bugs and feature requests
- **GitHub Discussions** - For questions and general discussion
- **Pull Request Comments** - For code-specific questions

### Questions

If you have questions:

1. Check the [documentation](../README.md)
2. Search existing issues and discussions
3. Ask in GitHub Discussions
4. Be specific and provide context

## Recognition

Contributors will be:

- Listed in the project's contributor list
- Credited in release notes for their contributions
- Acknowledged in documentation where appropriate

## License

By contributing, you agree that your contributions will be licensed under the Apache License 2.0, the same license as the project.

---

Thank you for contributing to Expression! Your efforts help make this library better for everyone.
