<?php

namespace App\Domain\Category\Dto;

class CreateCategoryDto
{
    public string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}