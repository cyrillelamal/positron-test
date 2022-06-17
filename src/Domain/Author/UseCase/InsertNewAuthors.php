<?php

namespace App\Domain\Author\UseCase;

use App\Domain\Author\Dto\CreateAuthorDto;
use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;

class InsertNewAuthors
{
    private EntityManagerInterface $entityManager;
    private AuthorRepository $repository;

    public function __construct(
        EntityManagerInterface $entityManager,
        AuthorRepository       $repository,
    )
    {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function __invoke(CreateAuthorDto ...$dtos): array
    {
        $names = array_map(fn(CreateAuthorDto $dto) => trim($dto->name), $dtos);

        $oldNames = $this->repository->findExistingNames(...$names);
        $newNames = array_filter($names, fn(string $name) => '' !== $name && !in_array($name, $oldNames));

        $authors = [];
        foreach ($newNames as $name) {
            $author = new Author($name);
            $authors[] = $author;
            $this->entityManager->persist($author);
        }

        $this->entityManager->flush();

        return $authors;
    }
}
