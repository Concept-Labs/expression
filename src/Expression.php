<?php
namespace Concept\Expression;

use Concept\Config\Traits\ConfigurableTrait;
use Concept\Prototype\PrototypableTrait;

class Expression implements ExpressionInterface
{
    use PrototypableTrait;
    use ConfigurableTrait;

    private array $expressions = [];
    private array $decorators = [];
    private array $context = [];

    public function withExpression($expression): ExpressionInterface
    {
        $clone = clone $this;
        $clone->expressions[] = $expression;

        return $clone;
    }

    public function withDecorator($decorator): ExpressionInterface
    {
        $clone = clone $this;
        $clone->decorators[] = $decorator;

        return $clone;
    }

    public function withSeparator(string $separator): ExpressionInterface
    {
        $clone = clone $this;
        $clone->decorators[] = function ($expression) use ($separator) {
            return implode($separator, $expression);
        };

        return $clone;
    }

    public function withContext(array $context): ExpressionInterface
    {
        $clone = clone $this;
        $clone->context = $context;

        return $clone;
    }

    public function __toString()
    {
        return $this->evaluate();
    }

    public function evaluate(array $context = []): string
    {
        $expression = implode(' ', $this->expressions);
        
        return $this->decorate($expression);
    }

    protected function decorate($expression)
    {
        foreach ($this->getDecorators() as $decorator) {
            $expression = $decorator->decorate($expression);
        }

        return $expression;
    }
    
    protected function getExpressions(): array
    {
        return $this->expressions;
    }

    protected function getDecorators(): array
    {
        return $this->decorators;
    }

    protected function getContext(): array
    {
        return $this->context;
    }
}