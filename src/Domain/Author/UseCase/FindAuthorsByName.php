<?php

namespace App\Domain\Author\UseCase;

use App\Entity\Author;
use App\Repository\AuthorRepository;

class FindAuthorsByName
{
    private AuthorRepository $repository;

    public function __construct(
        AuthorRepository $repository,
    )
    {
        $this->repository = $repository;
    }

    /**
     * @param string ...$names
     * @return Author[]
     */
    public function __invoke(string ...$names): array
    {
        return $this->repository->findAuthorsByName(...$names);
    }
}
