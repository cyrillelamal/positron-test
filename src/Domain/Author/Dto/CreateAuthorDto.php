<?php

namespace App\Domain\Author\Dto;

class CreateAuthorDto
{
    public string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
