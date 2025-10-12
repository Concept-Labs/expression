<?php

namespace Concept\Expression\Decorator;

use Concept\Expression\Exception\InvalidArgumentException;
use Concept\Expression\ExpressionInterface;

abstract class Decorator implements DecoratorInterface
{

   /**
     * Wrapper
     * 
     * @param string|Stringable|ExpressionInterface $left //Stringable php 8.0
     * @param string|Stringable|ExpressionInterface|null $right //Stringable php 8.0
     * @return callable
     */
    public static function wrapper($left, $right = null): callable
    {
        if (!is_string($left) && !$left instanceof ExpressionInterface && !$left instanceof \Stringable) {
            //php 8.0: check Stringable interface
            throw new InvalidArgumentException('Invalid left wrapper. Must be a string or ExpressionInterface.');
        }

        if ($right && !is_string($right) && !$right instanceof ExpressionInterface && !$right instanceof \Stringable) {
            //php 8.0: check Stringable interface
            throw new InvalidArgumentException('Invalid right wrapper. Must be a string or ExpressionInterface.');
        }

        return fn($value) => ($left ?? '')  . $value . ($right ?? $left ?? '');
    }

    /**
     * Joiner
     * 
     * @param string|Stringable|ExpressionInterface $separator //Stringable php 8.0
     * @param callable|null $itemWrapper 
     * 
     * @return callable
     */
    public static function joiner($separator): callable
    {
        if (!is_string($separator) && !$separator instanceof ExpressionInterface && !$separator instanceof \Stringable) {
            //php 8.0: check Stringable interface
            throw new InvalidArgumentException('Invalid separator. Must be a string or ExpressionInterface.');
        }
        return fn($values) => join( $separator, $values );
    }

}
