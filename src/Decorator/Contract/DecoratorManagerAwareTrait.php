<?php
namespace Concept\Expression\Decorator\Contract;

use Concept\Expression\Decorator\DecoratorManagerInterface;

trait DecoratorManagerAwareTrait
{
    /**
     * @var DecoratorManagerInterface
     */
    protected $decoratorManager;

    /**
     * {@inheritdoc}
     */
    public function withDecoratorManager(DecoratorManagerInterface $decoratorManager): void
    {
        $this->decoratorManager = $decoratorManager;
    }
}