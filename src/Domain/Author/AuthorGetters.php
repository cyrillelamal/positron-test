<?php

namespace App\Domain\Author;

use App\Domain\Book\Book;
use Doctrine\Common\Collections\Collection;

trait AuthorGetters
{
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return Collection<int, Book>
     */
    public function getBooks(): Collection
    {
        return $this->books;
    }
}
