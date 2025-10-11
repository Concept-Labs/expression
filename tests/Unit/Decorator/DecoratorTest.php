<?php

use Concept\Expression\Decorator\Decorator;
use Concept\Expression\Exception\InvalidArgumentException;

require_once __DIR__ . '/../../Helpers.php';

use function Concept\Expression\Tests\createExpression;

describe('Decorator Wrapper', function () {
    
    it('creates wrapper callable', function () {
        $wrapper = Decorator::wrapper('(', ')');
        
        expect($wrapper)->toBeCallable();
    });

    it('wraps value with left and right', function () {
        $wrapper = Decorator::wrapper('(', ')');
        
        expect($wrapper('value'))->toBe('(value)');
    });

    it('wraps value with left only (uses left for both sides)', function () {
        $wrapper = Decorator::wrapper('"');
        
        expect($wrapper('value'))->toBe('"value"');
    });

    it('wraps with different delimiters', function () {
        $wrapper = Decorator::wrapper('<', '>');
        
        expect($wrapper('tag'))->toBe('<tag>');
    });

    it('wraps empty value', function () {
        $wrapper = Decorator::wrapper('[', ']');
        
        expect($wrapper(''))->toBe('[]');
    });

    it('wraps with expression as left parameter', function () {
        $leftExpr = createExpression();
        $leftExpr->push('(');
        
        $wrapper = Decorator::wrapper($leftExpr, ')');
        
        expect($wrapper('value'))->toBe('(value)');
    });

    it('throws exception for invalid left wrapper', function () {
        Decorator::wrapper(['array'], ')');
    })->throws(InvalidArgumentException::class, 'Invalid left wrapper');

    it('throws exception for invalid right wrapper', function () {
        Decorator::wrapper('(', ['array']);
    })->throws(InvalidArgumentException::class, 'Invalid right wrapper');

    it('accepts null as right parameter', function () {
        $wrapper = Decorator::wrapper('(', null);
        
        expect($wrapper('value'))->toBe('(value(');
    });
});

describe('Decorator Joiner', function () {
    
    it('creates joiner callable', function () {
        $joiner = Decorator::joiner(', ');
        
        expect($joiner)->toBeCallable();
    });

    it('joins array with separator', function () {
        $joiner = Decorator::joiner(', ');
        
        expect($joiner(['a', 'b', 'c']))->toBe('a, b, c');
    });

    it('joins with space separator', function () {
        $joiner = Decorator::joiner(' ');
        
        expect($joiner(['SELECT', 'column']))->toBe('SELECT column');
    });

    it('joins with complex separator', function () {
        $joiner = Decorator::joiner(' AND ');
        
        expect($joiner(['condition1', 'condition2']))->toBe('condition1 AND condition2');
    });

    it('joins empty array', function () {
        $joiner = Decorator::joiner(', ');
        
        expect($joiner([]))->toBe('');
    });

    it('joins single element', function () {
        $joiner = Decorator::joiner(', ');
        
        expect($joiner(['single']))->toBe('single');
    });

    it('joins with expression as separator', function () {
        $separator = createExpression();
        $separator->push(' OR ');
        
        $joiner = Decorator::joiner($separator);
        
        expect($joiner(['a', 'b']))->toBe('a OR b');
    });

    it('throws exception for invalid separator', function () {
        Decorator::joiner(['array']);
    })->throws(InvalidArgumentException::class, 'Invalid separator');
});

describe('Decorator Integration', function () {
    
    it('wrapper and joiner work together', function () {
        $wrapper = Decorator::wrapper('`');
        $joiner = Decorator::joiner(', ');
        
        $values = ['name', 'email', 'status'];
        $wrapped = array_map($wrapper, $values);
        $result = $joiner($wrapped);
        
        expect($result)->toBe('`name`, `email`, `status`');
    });

    it('can create SQL-like decorators', function () {
        $columnWrapper = Decorator::wrapper('`');
        $joiner = Decorator::joiner(', ');
        
        $columns = ['id', 'username', 'created_at'];
        $wrappedColumns = array_map($columnWrapper, $columns);
        $columnList = $joiner($wrappedColumns);
        
        $selectWrapper = Decorator::wrapper('SELECT ', ' FROM users');
        $sql = $selectWrapper($columnList);
        
        expect($sql)->toBe('SELECT `id`, `username`, `created_at` FROM users');
    });

    it('can create nested structures', function () {
        $itemWrapper = Decorator::wrapper('(', ')');
        $joiner = Decorator::joiner(', ');
        
        $values1 = ['1', "'John'", "'john@example.com'"];
        $values2 = ['2', "'Jane'", "'jane@example.com'"];
        
        $row1 = $itemWrapper($joiner($values1));
        $row2 = $itemWrapper($joiner($values2));
        
        $rows = $joiner([$row1, $row2]);
        
        expect($rows)->toBe("(1, 'John', 'john@example.com'), (2, 'Jane', 'jane@example.com')");
    });
});
