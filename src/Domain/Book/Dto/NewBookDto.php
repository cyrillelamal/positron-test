<?php

namespace App\Domain\Book\Dto;

use DateTimeInterface;

class NewBookDto
{
    public ?string $title = null;

    public ?string $isbn = null;

    public int $pageCount = 0;

    public ?DateTimeInterface $publishedDate = null;

    public ?string $thumbnailUrl = null;

    public ?string $shortDescription = null;

    public ?string $longDescription = null;

    public ?string $status = null;

    /**
     * @var string[]
     */
    public array $authors = [];

    /**
     * @var string[]
     */
    public array $categories = [];
}
