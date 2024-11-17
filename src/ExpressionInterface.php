<?php
namespace Concept\Expression;

use Concept\Config\ConfigurableInterface;
use Concept\Di\InjectableInterface;

interface ExpressionInterface extends InjectableInterface, ConfigurableInterface
{
    public function evaluate(array $context = []): string;
}