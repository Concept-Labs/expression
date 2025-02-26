<?php

namespace Concept\Expression\Decorator;

use Concept\Expression\ExpressionInterface;
use Concept\Singularity\Contract\Lifecycle\PrototypeInterface;
use Traversable;

class DecoratorManager implements DecoratorManagerInterface, PrototypeInterface
{

    /**
     * The decorators
     * Callables that accept a expression string and return a decorated string
     * 
     * @var callable[]
     */
    private array $decorators = [];

    /**
     * The item decorators
     * Callables that accept a item string and return a decorated string
     * 
     * @var callable[]
     */
    private array $itemDecorators = [];

    /**
     * The join decorator
     * Callable that accepts an array of items and returns a decorated string
     * 
     * @var callable|null
     */
    private $joinDecorator = null;

    public function prototype(): static
    {
        return (clone $this)->reset();
    }

    public function __clone()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function reset(): static
    {
        $this->decorators = [];
        $this->itemDecorators = [];
        $this->joinDecorator = null;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function join($separator): static
    {
        return $this->setJoinDecorator(fn (array $items) => implode($separator, $items));
    }

    /**
     * {@inheritDoc}
     */
    public function wrap($left, $right = null): static
    {
        return $this->addDecorator(Decorator::wrapper($left, $right));
    }

    /**
     * {@inheritDoc}
     */
    public function wrapItem($left, $right = null): static
    {
        return $this->addItemDecorator(Decorator::wrapper($left, $right));
    }

    /**
     * {@inheritDoc}
     */
    public function setJoinDecorator(callable $decorator): static
    {
        $this->joinDecorator = $decorator;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addDecorator(callable ...$decorator): static
    {
        array_push($this->decorators, ...$decorator);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addItemDecorator(callable ...$decorator): static
    {
        array_push($this->itemDecorators, ...$decorator);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function applyDecorations(ExpressionInterface $expression): string
    {
        $items = $this->decorateItems($expression);
        $join = $this->getJoinDecorator();
        $string = $join($items);
        foreach ($this->getDecorators() as $decorator) {
            $string = $decorator($string);
        }

        return $string;
    }

    /**
     * Decorate the items
     * 
     * @param Traversable $items
     * @return array
     */
    protected function decorateItems(Traversable $items): array
    {
        $decoratedItems = [];
        foreach ($items as $item) {
            $itemString = (string)$item;
            foreach ($this->getItemDecorators() as $decorator) {
                $itemString = $decorator($itemString);
            }
            $decoratedItems[] = $itemString;
        }
        
        return $decoratedItems;
    }

    /**
     * Iterate over the item decorators
     */
    protected function getItemDecorators(): Traversable
    {
        yield from $this->itemDecorators;
    }

    /**
     * Get the join decorator
     * 
     * @return callable|null
     */
    protected function getJoinDecorator(): ?callable
    {
        return $this->joinDecorator ?? fn (array $items) => implode(' ', $items);
    }

    /**
     * Iterate over the decorators
     */
    protected function getDecorators(): Traversable
    {
        yield from $this->decorators;
    }
}
