<?php

namespace App\Domain\Author\Dto;

class NewAuthorDto
{
    public string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
