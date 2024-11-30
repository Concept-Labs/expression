<?php

namespace Concept\Expression\Decorator;

use Concept\Expression\ExpressionInterface;

interface DecoratorManagerInterface
{
    /**
     * Decorate
     * Decorate the expression with the given decorators
     * 
     * @param ExpressionInterface $expression
     * @return string
     */
    public function applyDecorations(ExpressionInterface $expression): string;

    /**
     * Join
     * Set joiner decorator
     * Used to join items
     * 
     * @param cllable $decorator
     * @return self
     */
    public function setJoinDecorator(callable $decorator): self;

    /**
     * Add the given decorators
     * 
     * @param callable ...$decorator
     * @return self
     */
    public function addDecorator(callable ...$decorator): self;

    /**
     * Add Item Decorator
     * Add the given item decorators
     * 
     * @param callable ...$decorator
     * @return self
     */
    public function addItemDecorator(callable ...$decorator): self;

    /**
     * Join
     * Set joiner decorator
     * Used to join items
     * 
     * @param string|Stringable|ExpressionInterface $separator
     * @return self
     */
    public function join($separator): self;

    /**
     * Wrapper
     * Wrap the expression with the given left and right strings/expressions
     * 
     * @param string|Stringable|ExpressionInterface $left //Stringable php 8.0
     * @param string|Stringable|ExpressionInterface|null $right //Stringable php 8.0
     * @return callable
     */
    public function wrap($left, $right = null): self;

    /**
     * Wrap Item
     * Wrap the item with the given left and right strings/expressions
     * 
     * @param string|Stringable|ExpressionInterface $left //Stringable php 8.0
     * @param string|Stringable|ExpressionInterface|null $right //Stringable php 8.0
     * @return self
     */
    public function wrapItem($left, $right = null): self;
}
