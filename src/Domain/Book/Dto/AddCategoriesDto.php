<?php

namespace App\Domain\Book\Dto;

class AddCategoriesDto
{
    public int $bookId;

    /**
     * @var string[]
     */
    public array $categories;

    public function __construct(int $bookId, array $categories = [])
    {
        $this->bookId = $bookId;
        $this->categories = $categories;
    }
}
