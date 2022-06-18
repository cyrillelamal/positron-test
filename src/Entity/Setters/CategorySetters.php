<?php

namespace App\Entity\Setters;

trait CategorySetters
{
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
