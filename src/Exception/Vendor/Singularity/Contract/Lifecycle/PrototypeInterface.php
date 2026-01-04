<?php
namespace Concept\Singularity\Contract\Lifecycle;

interface PrototypeInterface
{
    /**
     * Create a prototype (clone) of the object
     * 
     * @return static
     */
    public function prototype(): static;
}
