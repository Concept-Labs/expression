<?php

namespace Concept\Expression\Decorator;

use Concept\Di\InjectableInterface;
use Concept\Di\InjectableTrait;
use Concept\Expression\ExpressionInterface;
use Concept\Prototype\ResetableInterface;
use Traversable;

class DecoratorManager implements DecoratorManagerInterface, InjectableInterface, ResetableInterface
{
    use InjectableTrait;

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

    /**
     * {@inheritDoc}
     */
    public function reset(): self
    {
        $this->decorators = [];
        $this->itemDecorators = [];
        $this->joinDecorator = null;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function join($separator): self
    {
        return $this->setJoinDecorator(fn (array $items) => implode($separator, $items));
    }

    /**
     * {@inheritDoc}
     */
    public function wrap($left, $right = null): self
    {
        return $this->addDecorator(Decorator::wrapper($left, $right));
    }

    /**
     * {@inheritDoc}
     */
    public function wrapItem($left, $right = null): self
    {
        return $this->addItemDecorator(Decorator::wrapper($left, $right));
    }

    /**
     * {@inheritDoc}
     */
    public function setJoinDecorator(callable $decorator): self
    {
        $this->joinDecorator = $decorator;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addDecorator(callable ...$decorator): self
    {
        array_push($this->decorators, ...$decorator);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addItemDecorator(callable ...$decorator): self
    {
        array_push($this->itemDecorators, ...$decorator);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function applyDecorations(ExpressionInterface $expression): string
    {
        $items = iterator_to_array($this->decorateItems($expression));
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
     * @return Traversable
     */
    protected function decorateItems(Traversable $items)
    {
        foreach ($items as $item) {
            $itemString = (string)$item;
            foreach ($this->getItemDecorators() as $decorator) {
                $itemString = $decorator($itemString);
            }
            yield $itemString;
        }
        
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
