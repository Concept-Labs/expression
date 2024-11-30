<?php
namespace Concept\Expression;

use IteratorAggregate;

interface ExpressionInterface extends IteratorAggregate//, Stringable
{

    /**
     * Get the expression as a string
     * 
     * @return string
     */
    public function __toString(): string;
     
    /**
     * Add expressions to the current expression.
     *
     * @param mixed ...$expressions
     * @return self
     */
    //public function add(...$expressions): self;
    /**
     * @see add()
     */
    public function push(...$expressions): self;

    /**
     * Add expressions to the current expression.
     * @shortcut: withExpression()
     *
     * @param mixed ...$expressions
     * @return self
     */
    public function unshift(...$expressions): self;

    /**
     * Add an expression to the chain of expressions
     * Keep the oiginal object immutable
     * 
     * @param string|Stringable ...$expression
     * 
     * @return self
     */ 
    //public function withExpression(...$expression): self;

    /**
     * Set the expression decorator
     * 
     * @param callable ...$decorator
     * 
     * @return self
     * 
     * @throws \InvalidArgumentException
     */
    public function decorate(callable ...$decorator): self;

    /**
     * Set the join decorator
     * 
     * @param callable $decorator
     * 
     * @return self
     * 
     * @throws \InvalidArgumentException
     */
    public function decorateJoin(callable $decorator): ExpressionInterface;

    /**
     * Set the item decorator
     * 
     * @param callable $decorator
     * 
     * @return self
     * 
     * @throws \InvalidArgumentException
     */
    public function decorateItem(callable ...$decorator): ExpressionInterface;

    /**
     * Set the context for the expression
     * Use the context to interpolate the expression
     * 
     * @param array|ArrayAccess $context
     * 
     * @return string
     */
    public function withContext(array $context): self;

    /**
     * Get the context for the expression
     * 
     * @return array
     */
    public function count(): int;

    /**
     * Reset the expression
     */
    public function reset(): self;

    /**
     * Shortcuts
     */

    /**
     * Join the expression with the given separator
     * 
     * @param string|Stringable|ExpressionInterface $separator
     * 
     * @return self
     */
    public function join($separator): self;

    /**
     * Wrap the expression with the given left and right strings/expressions
     * 
     * @param string|Stringable|ExpressionInterface $left
     * @param string|Stringable|ExpressionInterface|null $right
     * 
     * @return self
     */
    public function wrap($left, $right = null): self;

    /**
     * Wrap the expression with the given left and right strings/expressions
     * 
     * @param string|Stringable|ExpressionInterface $left
     * @param string|Stringable|ExpressionInterface|null $right
     * 
     * @return self
     */
    public function wrapItem($left, $right = null): self;

}