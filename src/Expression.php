<?php
namespace Concept\Expression;

use Concept\Di\InjectableInterface;
use Concept\Di\InjectableTrait;
use Concept\Expression\Decorator\DecoratorManagerInterface;
use Traversable;

class Expression implements ExpressionInterface, InjectableInterface
{
    use InjectableTrait;

    private ?DecoratorManagerInterface $decoratorManager = null;
    private ?DecoratorManagerInterface $decoratorManagerPrototype = null;

    /**
     * The expressions
     * 
     * @var string[]|\Stringable[]|ExpressionInterface[]
     */
    private array $expressions = [];

    /**
     * The context
     * 
     * @var string[]
     */ 
    private array $context = [];

    public function __construct(DecoratorManagerInterface $decoratorManager)
    {
        $this->decoratorManagerPrototype = $decoratorManager;

        $this->init();
    }

    /**
     * Get the decorator manager
     * 
     * @return DecoratorManagerInterface
     */
    protected function getDecoratorManager(): DecoratorManagerInterface
    {
        if ($this->decoratorManager === null) {
            $this->decoratorManager = clone $this->decoratorManagerPrototype;
        }

        return $this->decoratorManager;
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        try {
            return $this->interpolate(
                $this->getDecoratorManager()->applyDecorations($this)
            );
        } catch (\Throwable $e) {
            return sprintf('[Error: %s]', $e->getMessage());
        }
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
     * Initialize the expression
     * Set the default decorators
     * 
     * @return self
     */
    protected function init(): self
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->expressions);
    }

    /**
     * Reset the expression
     * 
     * @return self
     */
    public function reset(): self
    {
        $this->expressions = [];

        return $this->init();
    }    

    /**
     * {@inheritDoc}
     */
    public function withExpression(...$expression): ExpressionInterface
    {
        $clone = clone $this;
        $clone->expressions = [];
        $clone->push(...$expression);

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function push(...$expressions): self
    {
        foreach($expressions as $item) {
            if (empty($item)) {
                continue;
            }
            if (!is_scalar($item) && !$item instanceof ExpressionInterface) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid expression of type %s. Must be scalar, Stringable, or ExpressionInterface.',
                    is_object($item) ? get_class($item) : gettype($item)
                ));
            }
            $this->expressions[] = $item;
            //$this->expressions[] = (string)$item; //convert now?
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function unshift(...$expressions): self
    {
        array_unshift($this->expressions, ...$expressions);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function decorate(callable ...$decorator): self
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
    public function decorateItem(callable ...$decorator): self
    {
        $this->getDecoratorManager()->addItemDecorator(...$decorator);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function join($separator): self
    {
        $this->getDecoratorManager()->join($separator);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function wrap($left, $right = null): self
    {
        $this->getDecoratorManager()->wrap($left, $right);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function wrapItem($left, $right = null): self
    {
        $this->getDecoratorManager()->wrapItem($left, $right);

        return $this;
    }
    

    /**
     * {@inheritDoc}
     */
    public function withContext(array $context): ExpressionInterface
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

    
}