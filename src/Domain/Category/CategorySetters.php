<?php

namespace App\Domain\Category;

trait CategorySetters
{
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
