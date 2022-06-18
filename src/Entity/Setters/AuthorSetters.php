<?php

namespace App\Entity\Setters;

trait AuthorSetters
{
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
