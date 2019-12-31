<?php

namespace VanNes;


class Entity
{
    private $name;
    private $ytitne;

    public function __construct(string $name, Ytitne $ytitne)
    {
        $this->name   = $name;
        $this->ytitne = $ytitne;
        $this->ytitne->alterEntity($this);
    }

    public function getName(): string
    {
        return $this->name;
    }
}
