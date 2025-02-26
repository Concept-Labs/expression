<?php
namespace Concept\Expression\Decorator\Contract;

use Concept\Expression\Decorator\DecoratorManagerInterface;

interface DecoratorManagerAwareInterface
{
    public function withDecoratorManager(DecoratorManagerInterface $decoratorManager): void;
}