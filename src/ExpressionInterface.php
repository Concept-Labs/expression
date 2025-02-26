<?php
namespace Concept\Expression;

use Concept\Singularity\Contract\Behavior\ResetableInterface;
use Concept\Singularity\Contract\Lifecycle\PrototypeInterface;
use IteratorAggregate;
use Stringable;

interface ExpressionInterface 
    extends 
        PrototypeInterface,
        ResetableInterface,
        IteratorAggregate, 
        Stringable
{

    /**
     * Get the expression as a string
     * 
     * @return string
     */
    public function __toString(): string;

    /**
     * Set the expression type
     * 
     * @param string $type
     * 
     * @return static
     */
    public function type(string $type): static;
     
    /**
     * Add expressions to the current expression.
     *
     * @param mixed ...$expressions
     * 
     * @return static
     */
    public function push(...$expressions): static;

    /**
     * Add expressions to the beginning of the current expression.
     *
     * @param mixed ...$expressions
     * 
     * @return static
     */
    public function unshift(...$expressions): static;

    /**
     * Set the expression decorator
     * 
     * @param callable ...$decorator
     * 
     * @return static
     * 
     * @throws \InvalidArgumentException
     */
    public function decorate(callable ...$decorator): static;

    /**
     * Set the join decorator
     * 
     * @param callable $decorator
     * 
     * @return static
     * 
     * @throws \InvalidArgumentException
     */
    public function decorateJoin(callable $decorator): ExpressionInterface;

    /**
     * Set the item decorator
     * 
     * @param callable $decorator
     * 
     * @return static
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
    public function withContext(array $context): static;

    /**
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * Shortcuts
     */

    /**
     * Join the expression with the given separator
     * 
     * @param string|Stringable|ExpressionInterface $separator
     * 
     * @return static
     */
    public function join($separator): static;

    /**
     * Wrap the expression with the given left and right strings/expressions
     * 
     * @param string|ExpressionInterface $left
     * @param string|Stringable|ExpressionInterface|null $right
     * 
     * @return static
     */
    public function wrap(
        string|Stringable|ExpressionInterface $left, 
        string|Stringable|ExpressionInterface|null $right = null
    ): static;

    /**
     * Wrap the expression with the given left and right strings/expressions
     * 
     * @param string|Stringable|ExpressionInterface $left
     * @param string|Stringable|ExpressionInterface|null $right
     * 
     * @return static
     */
    public function wrapItem(
        string|Stringable|ExpressionInterface $left, 
        string|Stringable|ExpressionInterface|null $right = null
    ): static;

}