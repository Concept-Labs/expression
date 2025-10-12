<?php

use Concept\Expression\Expression;
use Concept\Expression\Decorator\DecoratorManager;
use Concept\Expression\Decorator\Decorator;

require_once __DIR__ . '/../Helpers.php';

use function Concept\Expression\Tests\createExpression;

describe('Expression Decorators', function () {
    
    it('can add expression decorator', function () {
        $expression = createExpression();
        $expression->push('value');
        $expression->decorate(fn($str) => strtoupper($str));
        
        expect((string)$expression)->toBe('VALUE');
    });

    it('can add multiple expression decorators', function () {
        $expression = createExpression();
        $expression->push('value');
        $expression->decorate(
            fn($str) => strtoupper($str),
            fn($str) => "[$str]"
        );
        
        expect((string)$expression)->toBe('[VALUE]');
    });

    it('applies decorators in order', function () {
        $expression = createExpression();
        $expression->push('test');
        $expression->decorate(fn($str) => $str . '_1');
        $expression->decorate(fn($str) => $str . '_2');
        
        expect((string)$expression)->toBe('test_1_2');
    });
});

describe('Expression Item Decorators', function () {
    
    it('can add item decorator', function () {
        $expression = createExpression();
        $expression->push('a', 'b', 'c');
        $expression->decorateItem(fn($item) => strtoupper($item));
        
        expect((string)$expression)->toBe('A B C');
    });

    it('can add multiple item decorators', function () {
        $expression = createExpression();
        $expression->push('a', 'b');
        $expression->decorateItem(
            fn($item) => strtoupper($item),
            fn($item) => "[$item]"
        );
        
        expect((string)$expression)->toBe('[A] [B]');
    });

    it('applies item decorators before join', function () {
        $expression = createExpression();
        $expression->push('a', 'b');
        $expression->decorateItem(fn($item) => strtoupper($item));
        $expression->join(', ');
        
        expect((string)$expression)->toBe('A, B');
    });
});

describe('Expression Join Decorator', function () {
    
    it('can set custom join decorator', function () {
        $expression = createExpression();
        $expression->push('a', 'b', 'c');
        $expression->decorateJoin(fn($items) => implode(' AND ', $items));
        
        expect((string)$expression)->toBe('a AND b AND c');
    });

    it('can use join shortcut', function () {
        $expression = createExpression();
        $expression->push('a', 'b', 'c');
        $expression->join(', ');
        
        expect((string)$expression)->toBe('a, b, c');
    });

    it('join shortcut with complex separator', function () {
        $expression = createExpression();
        $expression->push('a', 'b', 'c');
        $expression->join(' OR ');
        
        expect((string)$expression)->toBe('a OR b OR c');
    });

    it('default join uses space', function () {
        $expression = createExpression();
        $expression->push('SELECT', 'column', 'FROM', 'table');
        
        expect((string)$expression)->toBe('SELECT column FROM table');
    });
});

describe('Expression Wrap Decorator', function () {
    
    it('can wrap expression with same delimiter', function () {
        $expression = createExpression();
        $expression->push('value');
        $expression->wrap('(', ')');
        
        expect((string)$expression)->toBe('(value)');
    });

    it('can wrap expression with left only', function () {
        $expression = createExpression();
        $expression->push('value');
        $expression->wrap('"');
        
        expect((string)$expression)->toBe('"value"');
    });

    it('can wrap expression with different delimiters', function () {
        $expression = createExpression();
        $expression->push('value');
        $expression->wrap('<', '>');
        
        expect((string)$expression)->toBe('<value>');
    });

    it('can wrap multiple times', function () {
        $expression = createExpression();
        $expression->push('value');
        $expression->wrap('(', ')');
        $expression->wrap('[', ']');
        
        expect((string)$expression)->toBe('[(value)]');
    });
});

describe('Expression Wrap Item Decorator', function () {
    
    it('can wrap items', function () {
        $expression = createExpression();
        $expression->push('a', 'b', 'c');
        $expression->wrapItem('(', ')');
        
        expect((string)$expression)->toBe('(a) (b) (c)');
    });

    it('can wrap items with quotes', function () {
        $expression = createExpression();
        $expression->push('name', 'email');
        $expression->wrapItem('"');
        $expression->join(', ');
        
        expect((string)$expression)->toBe('"name", "email"');
    });

    it('can combine item wrap with expression wrap', function () {
        $expression = createExpression();
        $expression->push('a', 'b');
        $expression->wrapItem('`');
        $expression->join(', ');
        $expression->wrap('(', ')');
        
        expect((string)$expression)->toBe('(`a`, `b`)');
    });
});

describe('Expression Complex Decorator Combinations', function () {
    
    it('can combine all decorator types', function () {
        $expression = createExpression();
        $expression->push('select', 'from', 'where');
        $expression->decorateItem(fn($item) => strtoupper($item));
        $expression->join(' -> ');
        $expression->wrap('[', ']');
        
        expect((string)$expression)->toBe('[SELECT -> FROM -> WHERE]');
    });

    it('applies decorations in correct order', function () {
        $expression = createExpression();
        $expression->push('a', 'b');
        
        // Item decorators applied first
        $expression->decorateItem(fn($item) => "item:$item");
        
        // Join decorator joins items
        $expression->join(' | ');
        
        // Expression decorator applied last
        $expression->decorate(fn($str) => "expr:$str");
        
        expect((string)$expression)->toBe('expr:item:a | item:b');
    });

    it('can build complex SQL-like expression', function () {
        $expression = createExpression();
        
        $columns = createExpression();
        $columns->push('name', 'email', 'status');
        $columns->wrapItem('`');
        $columns->join(', ');
        
        $expression->push('SELECT', $columns, 'FROM', '`users`');
        
        expect((string)$expression)->toBe('SELECT `name`, `email`, `status` FROM `users`');
    });
});

describe('Expression with Nested Expressions and Decorators', function () {
    
    it('nested expressions maintain their decorators', function () {
        $subExpr = createExpression();
        $subExpr->push('a', 'b');
        $subExpr->join(', ');
        $subExpr->wrap('(', ')');
        
        $mainExpr = createExpression();
        $mainExpr->push('VALUES', $subExpr);
        
        expect((string)$mainExpr)->toBe('VALUES (a, b)');
    });

    it('can create complex nested structures', function () {
        $columns = createExpression();
        $columns->push('id', 'name');
        $columns->join(', ');
        
        $values = createExpression();
        $values->push('1', "'John'");
        $values->join(', ');
        $values->wrap('(', ')');
        
        $insert = createExpression();
        $insert->push('INSERT INTO users', $columns, 'VALUES', $values);
        //$insert->wrapItem(fn($item) => $item);
        
        expect((string)$insert)->toContain('INSERT INTO users');
        expect((string)$insert)->toContain('id, name');
        expect((string)$insert)->toContain("(1, 'John')");
    });
});
