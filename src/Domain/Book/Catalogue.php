<?php

namespace App\Domain\Book;

use App\Entity\Book;
use Countable;

class Catalogue implements Countable
{
    private array $books = [];

    /**
     * Catalogue holds a set of unique books.
     * @param Book ...$books
     * @return static
     */
    public static function new(Book ...$books): self
    {
        $catalogue = new self();

        foreach ($books as $book) {
            $catalogue->addBook($book);
        }

        return $catalogue;
    }

    private function __construct()
    {
    }

    /**
     * Calculate set difference between two catalogues.
     * @param Catalogue $other the right operand.
     * @return static new catalogue.
     */
    public function minus(Catalogue $other): self
    {
        $books = array_filter(
            $this->getBooks(),
            fn(Book $book) => !$other->hasSimilarBook($book)
        );

        return Catalogue::new(...$books);
    }

    public function hasSimilarBook(Book $book): bool
    {
        foreach ($this->getBooks() as $b) {
            if ($book->isSimilarTo($b)) {
                return true;
            }
        }

        return false;
    }

    public function addBook(Book $book): void
    {
        if ($this->hasSimilarBook($book)) {
            return;
        }

        $this->books[] = $book;
    }

    /**
     * @return Book[]
     */
    public function getBooks(): array
    {
        return $this->books;
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->getBooks());
    }
}
