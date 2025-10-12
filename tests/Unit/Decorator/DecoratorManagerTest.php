<?php

use Concept\Expression\Decorator\DecoratorManager;
use Concept\Expression\Decorator\DecoratorManagerInterface;

require_once __DIR__ . '/../../Helpers.php';

use function Concept\Expression\Tests\createDecoratorManager;
use function Concept\Expression\Tests\createExpression;

describe('DecoratorManager Basic Operations', function () {
    
    it('can be instantiated', function () {
        $manager = createDecoratorManager();
        expect($manager)->toBeInstanceOf(DecoratorManagerInterface::class);
    });

    it('can add decorators', function () {
        $manager = createDecoratorManager();
        $result = $manager->addDecorator(fn($str) => strtoupper($str));
        
        expect($result)->toBe($manager);
    });

    it('can add multiple decorators at once', function () {
        $manager = createDecoratorManager();
        $result = $manager->addDecorator(
            fn($str) => strtoupper($str),
            fn($str) => "[$str]"
        );
        
        expect($result)->toBe($manager);
    });

    it('can add item decorators', function () {
        $manager = createDecoratorManager();
        $result = $manager->addItemDecorator(fn($item) => strtoupper($item));
        
        expect($result)->toBe($manager);
    });

    it('can set join decorator', function () {
        $manager = createDecoratorManager();
        $result = $manager->setJoinDecorator(fn($items) => implode(', ', $items));
        
        expect($result)->toBe($manager);
    });
});

describe('DecoratorManager Apply Decorations', function () {
    
    it('applies decorations to expression', function () {
        $manager = createDecoratorManager();
        $manager->addDecorator(fn($str) => strtoupper($str));
        
        $expression = createExpression();
        $expression->push('value');
        
        $result = $manager->applyDecorations($expression);
        expect($result)->toBe('VALUE');
    });

    it('applies item decorations', function () {
        $manager = createDecoratorManager();
        $manager->addItemDecorator(fn($item) => strtoupper($item));
        
        $expression = createExpression();
        $expression->push('a', 'b', 'c');
        
        $result = $manager->applyDecorations($expression);
        expect($result)->toBe('A B C');
    });

    it('applies join decorator', function () {
        $manager = createDecoratorManager();
        $manager->setJoinDecorator(fn($items) => implode(', ', $items));
        
        $expression = createExpression();
        $expression->push('a', 'b', 'c');
        
        $result = $manager->applyDecorations($expression);
        expect($result)->toBe('a, b, c');
    });

    it('uses default space join when no join decorator set', function () {
        $manager = createDecoratorManager();
        
        $expression = createExpression();
        $expression->push('a', 'b', 'c');
        
        $result = $manager->applyDecorations($expression);
        expect($result)->toBe('a b c');
    });

    it('applies decorations in correct order', function () {
        $manager = createDecoratorManager();
        
        // Item decorator first
        $manager->addItemDecorator(fn($item) => "[$item]");
        
        // Join decorator
        $manager->setJoinDecorator(fn($items) => implode(' + ', $items));
        
        // Expression decorator last
        $manager->addDecorator(fn($str) => "Result: $str");
        
        $expression = createExpression();
        $expression->push('a', 'b');
        
        $result = $manager->applyDecorations($expression);
        expect($result)->toBe('Result: [a] + [b]');
    });
});

describe('DecoratorManager Wrapper Methods', function () {
    
    it('wrap method adds expression decorator', function () {
        $manager = createDecoratorManager();
        $manager->wrap('(', ')');
        
        $expression = createExpression();
        $expression->push('value');
        
        $result = $manager->applyDecorations($expression);
        expect($result)->toBe('(value)');
    });

    it('wrapItem method adds item decorator', function () {
        $manager = createDecoratorManager();
        $manager->wrapItem('(', ')');
        
        $expression = createExpression();
        $expression->push('a', 'b');
        
        $result = $manager->applyDecorations($expression);
        expect($result)->toBe('(a) (b)');
    });

    it('join method sets join decorator', function () {
        $manager = createDecoratorManager();
        $manager->join(', ');
        
        $expression = createExpression();
        $expression->push('a', 'b', 'c');
        
        $result = $manager->applyDecorations($expression);
        expect($result)->toBe('a, b, c');
    });
});

describe('DecoratorManager Reset', function () {
    
    it('can be reset', function () {
        $manager = createDecoratorManager();
        $manager->addDecorator(fn($str) => strtoupper($str));
        $manager->addItemDecorator(fn($item) => "[$item]");
        $manager->setJoinDecorator(fn($items) => implode(', ', $items));
        
        $manager->reset();
        
        $expression = createExpression();
        $expression->push('a', 'b');
        
        // Should use default behavior after reset
        $result = $manager->applyDecorations($expression);
        expect($result)->toBe('a b');
    });

    it('reset returns self', function () {
        $manager = createDecoratorManager();
        $result = $manager->reset();
        
        expect($result)->toBe($manager);
    });
});

describe('DecoratorManager Clone and Prototype', function () {
    
    it('can be cloned', function () {
        $manager = createDecoratorManager();
        $manager->addDecorator(fn($str) => strtoupper($str));
        
        $clone = clone $manager;
        
        expect($clone)->toBeInstanceOf(DecoratorManagerInterface::class);
        expect($clone)->not->toBe($manager);
    });

    it('clone is not reset', function () {
        $manager = createDecoratorManager();
        $manager->addDecorator(fn($str) => strtoupper($str));
        
        $clone = clone $manager;
        
        $expression = createExpression();
        $expression->push('value');

        // Clone should not be reset and still apply the decorator
        $result = $clone->applyDecorations($expression);
        expect($result)->toBe('VALUE');
    });

    it('can create prototype', function () {
        $manager = createDecoratorManager();
        $prototype = $manager->prototype();
        
        expect($prototype)->toBeInstanceOf(DecoratorManagerInterface::class);
        expect($prototype)->not->toBe($manager);
    });

    it('prototype is reset', function () {
        $manager = createDecoratorManager();
        $manager->addDecorator(fn($str) => strtoupper($str));
        
        $prototype = $manager->prototype();
        
        $expression = createExpression();
        $expression->push('value');

        // Prototype should be reset and not apply any decorators
        $result = $prototype->applyDecorations($expression);
        expect($result)->toBe('value');
    });
});

describe('DecoratorManager Multiple Decorators', function () {
    
    it('can add multiple expression decorators', function () {
        $manager = createDecoratorManager();
        $manager->addDecorator(fn($str) => "1:$str");
        $manager->addDecorator(fn($str) => "2:$str");
        $manager->addDecorator(fn($str) => "3:$str");
        
        $expression = createExpression();
        $expression->push('test');
        
        $result = $manager->applyDecorations($expression);
        expect($result)->toBe('3:2:1:test');
    });

    it('can add multiple item decorators', function () {
        $manager = createDecoratorManager();
        $manager->addItemDecorator(fn($item) => strtoupper($item));
        $manager->addItemDecorator(fn($item) => "[$item]");
        
        $expression = createExpression();
        $expression->push('a', 'b');
        
        $result = $manager->applyDecorations($expression);
        expect($result)->toBe('[A] [B]');
    });

    it('can combine multiple decorator types', function () {
        $manager = createDecoratorManager();
        
        // Add item decorators
        $manager->addItemDecorator(fn($item) => strtoupper($item));
        $manager->addItemDecorator(fn($item) => "`$item`");
        
        // Set join
        $manager->join(', ');
        
        // Add expression decorators
        $manager->addDecorator(fn($str) => "SELECT $str");
        $manager->addDecorator(fn($str) => "$str FROM users");
        
        $expression = createExpression();
        $expression->push('id', 'name', 'email');
        
        $result = $manager->applyDecorations($expression);
        expect($result)->toBe('SELECT `ID`, `NAME`, `EMAIL` FROM users');
    });
});
