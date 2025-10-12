<?php

namespace Concept\Expression\Tests\Unit\PHPUnit;

use PHPUnit\Framework\TestCase;
use Concept\Expression\Expression;
use Concept\Expression\ExpressionInterface;
use Concept\Expression\Decorator\DecoratorManager;
use Concept\Expression\Exception\InvalidArgumentException;

class ExpressionTest extends TestCase
{
    private function createExpression(): ExpressionInterface
    {
        return new Expression(new DecoratorManager());
    }

    public function testCanBeInstantiated(): void
    {
        $expression = $this->createExpression();
        $this->assertInstanceOf(ExpressionInterface::class, $expression);
    }

    public function testIsEmptyWhenCreated(): void
    {
        $expression = $this->createExpression();
        $this->assertTrue($expression->isEmpty());
    }

    public function testCanPushScalarValues(): void
    {
        $expression = $this->createExpression();
        $expression->push('SELECT', 'column');
        
        $this->assertFalse($expression->isEmpty());
        $this->assertEquals('SELECT column', (string)$expression);
    }

    public function testCanPushMultipleExpressions(): void
    {
        $expression = $this->createExpression();
        $expression->push('SELECT', 'column', 'FROM', 'table');
        
        $this->assertEquals('SELECT column FROM table', (string)$expression);
    }

    public function testSkipsEmptyValues(): void
    {
        $expression = $this->createExpression();
        $expression->push('SELECT', '', null, 'column');
        
        $this->assertEquals('SELECT column', (string)$expression);
    }

    public function testCanPushNestedExpressions(): void
    {
        $expression = $this->createExpression();
        $subExpression = $this->createExpression();
        $subExpression->push('inner', 'expression');
        
        $expression->push('outer', $subExpression);
        
        $this->assertEquals('outer inner expression', (string)$expression);
    }

    public function testThrowsExceptionForInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        
        $expression = $this->createExpression();
        $expression->push(['array']);
    }

    public function testCanUnshiftExpressions(): void
    {
        $expression = $this->createExpression();
        $expression->push('column');
        $expression->unshift('SELECT');
        
        $this->assertEquals('SELECT column', (string)$expression);
    }

    public function testCanBeReset(): void
    {
        $expression = $this->createExpression();
        $expression->push('SELECT', 'column');
        
        $expression->reset();
        
        $this->assertTrue($expression->isEmpty());
        $this->assertEquals('', (string)$expression);
    }

    public function testCanBeCloned(): void
    {
        $expression = $this->createExpression();
        $expression->push('SELECT', 'column');
        
        $clone = clone $expression;
        $clone->push('FROM', 'table');
        
        $this->assertEquals('SELECT column', (string)$expression);
        $this->assertEquals('SELECT column FROM table', (string)$clone);
    }

    public function testCanCreatePrototype(): void
    {
        $expression = $this->createExpression();
        $expression->push('SELECT', 'column');
        
        $prototype = $expression->prototype();
        $prototype->push('FROM', 'table');
        
        $this->assertEquals('SELECT column', (string)$expression);
        $this->assertEquals('FROM table', (string)$prototype);
    }

    public function testCanSetType(): void
    {
        $expression = $this->createExpression();
        $expression->push('column')
                   ->type('select');
        
        $this->assertEquals('column', (string)$expression);
    }

    public function testIsIterable(): void
    {
        $expression = $this->createExpression();
        $expression->push('a', 'b', 'c');
        
        $items = [];
        foreach ($expression as $item) {
            $items[] = $item;
        }
        
        $this->assertEquals(['a', 'b', 'c'], $items);
    }

    public function testCanInterpolateContext(): void
    {
        $expression = $this->createExpression();
        $expression->push('SELECT', '{column}', 'FROM', '{table}');
        
        $contextExpression = $expression->withContext([
            'column' => 'name',
            'table' => 'users'
        ]);
        
        $this->assertEquals('SELECT name FROM users', (string)$contextExpression);
    }

    public function testContextReturnsNewInstance(): void
    {
        $expression = $this->createExpression();
        $expression->push('SELECT', '{column}');
        
        $contextExpression = $expression->withContext(['column' => 'name']);
        
        $this->assertEquals('SELECT {column}', (string)$expression);
        $this->assertEquals('SELECT name', (string)$contextExpression);
    }

    public function testCanAddDecorator(): void
    {
        $expression = $this->createExpression();
        $expression->push('value');
        $expression->decorate(fn($str) => strtoupper($str));
        
        $this->assertEquals('VALUE', (string)$expression);
    }

    public function testCanAddItemDecorator(): void
    {
        $expression = $this->createExpression();
        $expression->push('a', 'b', 'c');
        $expression->decorateItem(fn($item) => strtoupper($item));
        
        $this->assertEquals('A B C', (string)$expression);
    }

    public function testCanSetJoinDecorator(): void
    {
        $expression = $this->createExpression();
        $expression->push('a', 'b', 'c');
        $expression->decorateJoin(fn($items) => implode(' AND ', $items));
        
        $this->assertEquals('a AND b AND c', (string)$expression);
    }

    public function testCanUseJoinShortcut(): void
    {
        $expression = $this->createExpression();
        $expression->push('a', 'b', 'c');
        $expression->join(', ');
        
        $this->assertEquals('a, b, c', (string)$expression);
    }

    public function testCanWrapExpression(): void
    {
        $expression = $this->createExpression();
        $expression->push('value');
        $expression->wrap('(', ')');
        
        $this->assertEquals('(value)', (string)$expression);
    }

    public function testCanWrapItems(): void
    {
        $expression = $this->createExpression();
        $expression->push('a', 'b', 'c');
        $expression->wrapItem('(', ')');
        
        $this->assertEquals('(a) (b) (c)', (string)$expression);
    }

    public function testComplexSQLLikeExpression(): void
    {
        $expression = $this->createExpression();
        
        $columns = $this->createExpression();
        $columns->push('name', 'email', 'status');
        $columns->wrapItem('`');
        $columns->join(', ');
        
        $expression->push('SELECT', $columns, 'FROM', '`users`');
        
        $this->assertEquals('SELECT `name`, `email`, `status` FROM `users`', (string)$expression);
    }
}
