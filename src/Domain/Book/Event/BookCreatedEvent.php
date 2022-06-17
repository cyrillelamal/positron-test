<?php

namespace App\Domain\Book\Event;

use App\Entity\Book;

class BookCreatedEvent
{
    public const NAME = 'book.created';

    private Book $book;

    public function __construct(Book $book)
    {
        $this->book = $book;
    }

    public function getBook(): Book
    {
        return $this->book;
    }
}
