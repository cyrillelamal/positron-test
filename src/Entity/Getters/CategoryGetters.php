<?php

namespace App\Entity\Getters;

use App\Entity\Book;
use Doctrine\Common\Collections\Collection;

trait CategoryGetters
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
