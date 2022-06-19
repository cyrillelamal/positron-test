<?php

namespace App\Domain\Book\Dto;

class StoreThumbnailDto
{
    public int $bookId;

    public ?string $url;

    public function __construct(int $bookId, ?string $url = null)
    {
        $this->bookId = $bookId;
        $this->url = $url;
    }
}
