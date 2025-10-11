<?php

namespace Concept\Expression\Tests;

use Concept\Expression\Expression;
use Concept\Expression\ExpressionInterface;
use Concept\Expression\Decorator\DecoratorManager;
use Concept\Expression\Decorator\DecoratorManagerInterface;

/**
 * Helper functions for tests
 */
function createExpression(): ExpressionInterface
{
    return new Expression(new DecoratorManager());
}

function createDecoratorManager(): DecoratorManagerInterface
{
    return new DecoratorManager();
}
