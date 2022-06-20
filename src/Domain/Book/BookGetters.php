<?php

namespace App\Domain\Book;

use App\Domain\Author\Author;
use App\Domain\Category\Category;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;

trait BookGetters
{
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getIsbn(): ?string
    {
        return $this->isbn;
    }

    public function getPageCount(): ?int
    {
        return $this->pageCount;
    }

    public function getPublishedDate(): ?DateTimeInterface
    {
        return $this->publishedDate;
    }

    public function getThumbnailUrl(): ?string
    {
        return $this->thumbnailUrl;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function getLongDescription(): ?string
    {
        return $this->longDescription;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    /**
     * @return Collection<int, Author>
     */
    public function getAuthors(): Collection
    {
        return $this->authors;
    }
}
