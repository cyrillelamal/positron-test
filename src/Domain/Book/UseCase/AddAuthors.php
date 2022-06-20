<?php

namespace App\Domain\Book\UseCase;

use App\Domain\Book\Dto\AddAuthorsDto;
use App\Domain\Author\Author;
use App\Domain\Author\Repository\AuthorRepository;
use App\Domain\Book\Repository\BookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AddAuthors implements MessageHandlerInterface
{
    private AuthorRepository $authorRepository;
    private BookRepository $bookRepository;

    public function __construct(
        AuthorRepository $authorRepository,
        BookRepository   $bookRepository,
    )
    {
        $this->authorRepository = $authorRepository;
        $this->bookRepository = $bookRepository;
    }

    public function __invoke(AddAuthorsDto $data): void
    {
        $book = $this->bookRepository->find($data->bookId);

        if (!$book) {
            return;
        }

        $authors = $this->upsert(...$data->authors);

        foreach ($authors as $author) {
            $book->addAuthor($author);
        }

        $this->bookRepository->add($book, true);
    }

    /**
     * @param string ...$names
     * @return Author[]
     */
    private function upsert(string ...$names): array
    {
        /** @var Author[] $authors */
        $authors = (new ArrayCollection($names))
            ->filter(fn(string $name) => '' !== trim($name))
            ->map(fn(string $name) => new Author($name))
            ->toArray();

        $this->authorRepository->upsert(...$authors);

        return $this->reloadAuthors(...$authors);
    }

    /**
     * @param Author ...$authors
     * @return Author[]
     */
    private function reloadAuthors(Author ...$authors): array
    {
        return $this->authorRepository->findByNames(
            ...array_map(fn(Author $author) => $author->getName(), $authors),
        );
    }
}
