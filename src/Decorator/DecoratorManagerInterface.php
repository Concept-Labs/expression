<?php

namespace Concept\Expression\Decorator;

use Concept\Expression\ExpressionInterface;
use Concept\Prototype\ResetableInterface;

interface DecoratorManagerInterface extends ResetableInterface
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
     * @return static
     */
    public function setJoinDecorator(callable $decorator): static;

    /**
     * Add the given decorators
     * 
     * @param callable ...$decorator
     * @return static
     */
    public function addDecorator(callable ...$decorator): static;

    /**
     * Add Item Decorator
     * Add the given item decorators
     * 
     * @param callable ...$decorator
     * @return static
     */
    public function addItemDecorator(callable ...$decorator): static;

    /**
     * Join
     * Set joiner decorator
     * Used to join items
     * 
     * @param string|Stringable|ExpressionInterface $separator
     * @return static
     */
    public function join($separator): static;

    /**
     * Wrapper
     * Wrap the expression with the given left and right strings/expressions
     * 
     * @param string|Stringable|ExpressionInterface $left //Stringable php 8.0
     * @param string|Stringable|ExpressionInterface|null $right //Stringable php 8.0
     * @return callable
     */
    public function wrap($left, $right = null): static;

    /**
     * Wrap Item
     * Wrap the item with the given left and right strings/expressions
     * 
     * @param string|Stringable|ExpressionInterface $left //Stringable php 8.0
     * @param string|Stringable|ExpressionInterface|null $right //Stringable php 8.0
     * @return static
     */
    public function wrapItem($left, $right = null): static;
}
