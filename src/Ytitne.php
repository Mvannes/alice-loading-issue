<?php

namespace VanNes;


class Ytitne
{
    private $name;
    private $entity;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function alterEntity(Entity $entity): void
    {
        if (null !== $this->entity && $this->entity !== $entity) {
            var_dump($this->entity->getName(), $entity->getName());

            // Switch the comments on line 22 and 23 to see the different objects.
            throw new \InvalidArgumentException('Can not alter this entity, as one exists');
//            return;
        }
        $this->entity = $entity;
    }
}
