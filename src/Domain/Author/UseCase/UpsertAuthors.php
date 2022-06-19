<?php

namespace App\Domain\Author\UseCase;

use App\Domain\Author\Dto\NewAuthorDto;
use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class UpsertAuthors
{
    private AuthorRepository $repository;
    private DenormalizerInterface $denormalizer;
    private EntityManagerInterface $entityManager;

    public function __construct(
        AuthorRepository       $repository,
        DenormalizerInterface  $denormalizer,
        EntityManagerInterface $entityManager,
    )
    {
        $this->repository = $repository;
        $this->denormalizer = $denormalizer;
        $this->entityManager = $entityManager;
    }

    /**
     * @param NewAuthorDto ...$dtos
     * @return Author[] the affected authors managed by the entity manager.
     */
    public function __invoke(NewAuthorDto ...$dtos): array
    {
        /** @var Author[] $authors */
        $authors = (new ArrayCollection($dtos))
            ->filter(fn(NewAuthorDto $dto) => '' !== trim($dto->name))
            ->map(fn(NewAuthorDto $dto) => $this->denormalizer->denormalize($dto, Author::class))
            ->toArray();

        $this->entityManager->wrapInTransaction(function () use ($authors) {
            $this->repository->upsert(...$authors);
        });

        return $this->reloadAuthors(...$authors);
    }

    /**
     * @param Author ...$authors
     * @return Author[]
     */
    private function reloadAuthors(Author ...$authors): array
    {
        return $this->repository->findByNames(
            ...array_map(fn(Author $author) => $author->getName(), $authors)
        );
    }
}
