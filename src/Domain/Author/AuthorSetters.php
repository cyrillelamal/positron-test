<?php

namespace App\Domain\Author;

trait AuthorSetters
{
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
