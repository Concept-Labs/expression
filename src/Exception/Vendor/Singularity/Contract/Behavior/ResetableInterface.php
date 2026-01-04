<?php
namespace Concept\Singularity\Contract\Behavior;

interface ResetableInterface
{
    /**
     * Reset the object to its initial state
     * 
     * @return static
     */
    public function reset(): static;
}
