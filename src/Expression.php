<?php
namespace Concept\Expression;

use Concept\Expression\Exception\InvalidArgumentException;
use Concept\Expression\Decorator\DecoratorManager;
use Concept\Expression\Decorator\DecoratorManagerInterface;
use Traversable;

class Expression implements ExpressionInterface
{
    /**
     * The expressions
     * 
     * @var string[]|\Stringable[]|ExpressionInterface[]
     */
    private array $expressions = [];
    protected $_debug = true;

    /**
     * The context
     * 
     * @var string[]
     */ 
    private array $context = [];

    private ?string $type = null;

    private ?DecoratorManagerInterface $decoratorManager = null;

    /**
     * Invoke the expression
     * 
     * @param mixed ...$expressions
     * 
     * @return static
     */
    public function __invoke(...$expressions)
    {
        return $this->push(...$expressions);
    }

    /**
     * Clone the expression
     * 
     * @return static
     */
    public function __clone()
    {
        $this->decoratorManager = clone $this->decoratorManager;
    }

    public function prototype(): static
    {
        return clone $this;
    }

    /**
     * Reset the expression
     * 
     * @return static
     */
    public function reset(): static
    {
        $this->expressions = [];
        $this->context = [];
        $this->type = null;
        

        return $this;
    }

    /**
     * Get the decorator manager
     * 
     * @return DecoratorManagerInterface
     */
    protected function getDecoratorManager(): DecoratorManagerInterface
    {
        return $this->decoratorManager ??= new DecoratorManager();
    }

    

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        return $this->interpolate(
            $this->getDecoratorManager()->applyDecorations($this)
        );
    }

    public function getDebugString(): string
    {
        $this->wrap("{".strtoupper($this->type??'no-type').':', '}');

        return $this->__toString();
    }
    
    /**
     * {@inheritDoc}
     */
    public function getIterator(): Traversable
    {
        foreach ($this->expressions as $expression) {
            yield $expression;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isEmpty(): bool
    {
        return empty($this->expressions);
    }

    /**
     * {@inheritDoc}
     */
    public function push(...$expressions): static
    {
        foreach($expressions as $item) {
            if (empty($item) || ($item instanceof ExpressionInterface && $item->isEmpty())) {
                continue;
            }
            if (!is_scalar($item) && !$item instanceof ExpressionInterface) {
                throw new InvalidArgumentException(
                    'Invalid expression of type. Must be scalar, Stringable, or ExpressionInterface.',
                );
            }
            $this->expressions[] = $item;
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function unshift(...$expressions): static
    {
        array_unshift($this->expressions, ...$expressions);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function decorate(callable ...$decorator): static
    {
        $this->getDecoratorManager()->addDecorator(...$decorator);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function decorateJoin(callable $decorator): ExpressionInterface
    {
        $this->getDecoratorManager()->setJoinDecorator($decorator);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function decorateItem(callable ...$decorator): static
    {
        $this->getDecoratorManager()->addItemDecorator(...$decorator);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function join($separator): static
    {
        $this->getDecoratorManager()->join($separator);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function wrap($left, $right = null): static
    {
        $this->getDecoratorManager()->wrap($left, $right);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function wrapItem($left, $right = null): static
    {
        $this->getDecoratorManager()->wrapItem($left, $right);

        return $this;
    }
    

    /**
     * {@inheritDoc}
     */
    public function withContext(array $context): static
    {
        $clone = clone $this;
        $clone->context = $context;

        return $clone;
    }

    /**
     * Interpolate the expression with the context
     * 
     * @param string $expression
     * 
     * @return string
     */
    protected function interpolate(string $expression, array $defaults = []): string
    {
        $replacements = [];
        $context = array_merge($defaults, $this->getContext());
        foreach ($context as $key => $value) {
            if (is_scalar($value)) {
                $replacements["{{$key}}"] = $value;
            }
        }
    
        return strtr($expression, $replacements);
    } 

   
    /**
     * Get the expressions
     * 
     * @return string[]|\Stringable[]|ExpressionInterface[]
     */
    public function getExpressions(): array
    {
        return $this->expressions;
    }

    /**
     * Get the context
     * 
     * @return string[]
     */
    protected function getContext(): array
    {
        return $this->context;
    }

    public function type(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    
}