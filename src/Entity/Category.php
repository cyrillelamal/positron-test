<?php

namespace App\Entity;

use App\Entity\Getters\CategoryGetters;
use App\Entity\Setters\CategorySetters;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\UniqueConstraint(name: 'category_name', columns: ['name'])]
class Category
{
    use CategoryGetters;
    use CategorySetters;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['collation' => 'utf8_bin'])]
    private string $name;

    #[ORM\ManyToMany(targetEntity: Book::class, mappedBy: 'categories')]
    private Collection $books;

    public function __construct(string $name = '')
    {
        $this->books = new ArrayCollection();

        $this->name = $name;
    }

    public function addBook(Book $book): self
    {
        if (!$this->books->contains($book)) {
            $this->books[] = $book;
            $book->addCategory($this);
        }

        return $this;
    }

    public function removeBook(Book $book): self
    {
        if ($this->books->removeElement($book)) {
            $book->removeCategory($this);
        }

        return $this;
    }
}
