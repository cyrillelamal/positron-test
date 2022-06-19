<?php

namespace App\Domain\Book\Dto;

class AddAuthorsDto
{
    public int $bookId;

    /**
     * @var string[]
     */
    public array $authors;

    public function __construct(int $bookId, array $authors = [])
    {
        $this->bookId = $bookId;
        $this->authors = $authors;
    }
}
