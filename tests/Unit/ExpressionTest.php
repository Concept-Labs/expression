<?php

use Concept\Expression\Expression;
use Concept\Expression\ExpressionInterface;
use Concept\Expression\Decorator\DecoratorManager;
use Concept\Expression\Exception\InvalidArgumentException;

require_once __DIR__ . '/../Helpers.php';

use function Concept\Expression\Tests\createExpression;

describe('Expression Basic Operations', function () {
    
    it('can be instantiated', function () {
        $expression = createExpression();
        expect($expression)->toBeInstanceOf(ExpressionInterface::class);
    });

    it('is empty when created', function () {
        $expression = createExpression();
        expect($expression->isEmpty())->toBeTrue();
    });

    it('can push scalar values', function () {
        $expression = createExpression();
        $expression->push('SELECT', 'column');
        
        expect($expression->isEmpty())->toBeFalse();
        expect((string)$expression)->toBe('SELECT column');
    });

    it('can push multiple expressions at once', function () {
        $expression = createExpression();
        $expression->push('SELECT', 'column', 'FROM', 'table');
        
        expect((string)$expression)->toBe('SELECT column FROM table');
    });

    it('skips empty values when pushing', function () {
        $expression = createExpression();
        $expression->push('SELECT', '', null, 'column');
        
        expect((string)$expression)->toBe('SELECT column');
    });

    it('skips empty expressions when pushing', function () {
        $expression = createExpression();
        $emptyExpr = createExpression();
        $expression->push('SELECT', $emptyExpr, 'column');
        
        expect((string)$expression)->toBe('SELECT column');
    });

    it('can push nested expressions', function () {
        $expression = createExpression();
        $subExpression = createExpression();
        $subExpression->push('inner', 'expression');
        
        $expression->push('outer', $subExpression);
        
        expect((string)$expression)->toBe('outer inner expression');
    });

    it('throws exception for invalid expression type', function () {
        $expression = createExpression();
        $expression->push(['array']);
    })->throws(InvalidArgumentException::class);

    it('can unshift expressions', function () {
        $expression = createExpression();
        $expression->push('column');
        $expression->unshift('SELECT');
        
        expect((string)$expression)->toBe('SELECT column');
    });

    it('can unshift multiple expressions', function () {
        $expression = createExpression();
        $expression->push('table');
        $expression->unshift('SELECT', '*', 'FROM');
        
        expect((string)$expression)->toBe('SELECT * FROM table');
    });
});

describe('Expression Reset and Clone', function () {
    
    it('can be reset', function () {
        $expression = createExpression();
        $expression->push('SELECT', 'column')
                   ->type('select');
        
        $expression->reset();
        
        expect($expression->isEmpty())->toBeTrue();
        expect((string)$expression)->toBe('');
    });

    it('can be cloned', function () {
        $expression = createExpression();
        $expression->push('SELECT', 'column');
        
        $clone = clone $expression;
        $clone->push('FROM', 'table');
        
        expect((string)$expression)->toBe('SELECT column');
        expect((string)$clone)->toBe('SELECT column FROM table');
    });

    it('can create prototype', function () {
        $expression = createExpression();
        $expression->push('SELECT', 'column');
        
        $prototype = $expression->prototype();
        $prototype->push('FROM', 'table');
        
        expect((string)$expression)->toBe('SELECT column');
        expect((string)$prototype)->toBe('SELECT column FROM table');
    });

    it('clones decorator manager when cloned', function () {
        $expression = createExpression();
        $expression->push('value');
        $expression->wrap('(', ')');
        
        $clone = clone $expression;
        
        // Original should be wrapped
        expect((string)$expression)->toBe('(value)');
        
        // Clone should also be wrapped but changes shouldn't affect original
        expect((string)$clone)->toBe('(value)');
        
        // Add more decoration to clone
        $clone->wrap('[', ']');
        expect((string)$clone)->toBe('[(value)]');
        expect((string)$expression)->toBe('(value)');
    });
});

describe('Expression Type', function () {
    
    it('can set and use type', function () {
        $expression = createExpression();
        $expression->push('column')
                   ->type('select');
        
        expect((string)$expression)->toBe('column');
    });

    it('type is used in debug string', function () {
        $expression = createExpression();
        $expression->push('value')
                   ->type('test');
        
        $debug = $expression->getDebugString();
        expect($debug)->toContain('TEST');
    });

    it('shows no-type when type not set in debug', function () {
        $expression = createExpression();
        $expression->push('value');
        
        $debug = $expression->getDebugString();
        expect($debug)->toContain('NO-TYPE');
    });
});

describe('Expression Iterator', function () {
    
    it('is iterable', function () {
        $expression = createExpression();
        $expression->push('a', 'b', 'c');
        
        $items = [];
        foreach ($expression as $item) {
            $items[] = $item;
        }
        
        expect($items)->toBe(['a', 'b', 'c']);
    });

    it('iterates over nested expressions', function () {
        $expression = createExpression();
        $subExpr = createExpression();
        $subExpr->push('nested');
        
        $expression->push('outer', $subExpr);
        
        $items = [];
        foreach ($expression as $item) {
            $items[] = $item;
        }
        
        expect(count($items))->toBe(2);
        expect($items[0])->toBe('outer');
        expect($items[1])->toBeInstanceOf(ExpressionInterface::class);
    });
});

describe('Expression Context Interpolation', function () {
    
    it('can interpolate context variables', function () {
        $expression = createExpression();
        $expression->push('SELECT', '{column}', 'FROM', '{table}');
        
        $contextExpression = $expression->withContext([
            'column' => 'name',
            'table' => 'users'
        ]);
        
        expect((string)$contextExpression)->toBe('SELECT name FROM users');
    });

    it('returns new instance with context', function () {
        $expression = createExpression();
        $expression->push('SELECT', '{column}');
        
        $contextExpression = $expression->withContext(['column' => 'name']);
        
        // Original should not be affected
        expect((string)$expression)->toBe('SELECT {column}');
        expect((string)$contextExpression)->toBe('SELECT name');
    });

    it('only interpolates scalar values', function () {
        $expression = createExpression();
        $expression->push('{scalar}', '{array}');
        
        $contextExpression = $expression->withContext([
            'scalar' => 'value',
            'array' => ['not', 'interpolated']
        ]);
        
        expect((string)$contextExpression)->toBe('value {array}');
    });

    it('handles empty context', function () {
        $expression = createExpression();
        $expression->push('SELECT', '{column}');
        
        $contextExpression = $expression->withContext([]);
        
        expect((string)$contextExpression)->toBe('SELECT {column}');
    });
});
