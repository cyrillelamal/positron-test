<?php

namespace App\Entity;

use App\Domain\Book\Status;
use App\Entity\Getters\BookGetters;
use App\Entity\Setters\BookSetters;
use App\Repository\BookRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @Vich\Uploadable()
 */
#[ORM\Entity(repositoryClass: BookRepository::class)]
#[ORM\Index(fields: ['isbn'], name: 'book_isbn_idx')]
#[ORM\Index(fields: ['title'], name: 'book_title_idx')]
class Book
{
    use BookGetters;
    use BookSetters;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 511)]
    private ?string $title = null;

    #[ORM\Column(type: Types::STRING, length: 13, nullable: true)]
    private ?string $isbn = null;

    #[ORM\Column(type: Types::INTEGER)]
    private int $pageCount = 0;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $publishedDate = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?string $thumbnailUrl = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $shortDescription = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $longDescription = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $status = Status::PUBLISH;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $updatedAt = null;

    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'books')]
    private Collection $categories;

    #[ORM\ManyToMany(targetEntity: Author::class, inversedBy: 'books')]
    private Collection $authors;

    /**
     * @Vich\UploadableField(mapping="book_thumbnails", fileNameProperty="thumbnailUrl")
     */
    private ?File $thumbnailFile = null;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->authors = new ArrayCollection();
    }

    public function isSimilarTo(Book $other): bool
    {
        if ($other->hasSameIsbn($this)) {
            return $other->hasSimilarTitle($this);
        }

        return $other->hasSimilarTitle($this);
    }

    public function hasSameIsbn(Book $other): bool
    {
        return $this->hasIsbn()
            && $other->hasIsbn()
            && $this->getIsbn() === $other->getIsbn();
    }

    public function hasIsbn(): bool
    {
        return null !== $this->getIsbn();
    }

    public function hasNoIsbn(): bool
    {
        return !$this->hasIsbn();
    }

    public function hasSimilarTitle(Book $other): bool
    {
        return trim($this->getTitle()) === trim($other->getTitle());
    }

    /**
     * @return string[]
     */
    public function getCategoryNames(): array
    {
        return $this
            ->getCategories()
            ->map(fn(Category $category) => $category->getName())
            ->toArray();
    }

    public function hasNoCategories(): bool
    {
        return $this->getCategories()->count() < 1;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        $this->categories->removeElement($category);

        return $this;
    }

    public function addAuthor(Author $author): self
    {
        if (!$this->authors->contains($author)) {
            $this->authors[] = $author;
        }

        return $this;
    }

    public function removeAuthor(Author $author): self
    {
        $this->authors->removeElement($author);

        return $this;
    }

    /**
     * @return File|null
     */
    public function getThumbnailFile(): ?File
    {
        return $this->thumbnailFile;
    }

    /**
     * @param File $file
     */
    public function setThumbnailFile(File $file): void
    {
        $this->thumbnailFile = $file;

        $this->updatedAt = new DateTime();
    }
}
