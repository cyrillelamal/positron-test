<?php

namespace App\Domain\Category\UseCase;

use App\Repository\CategoryRepository;

class FindCategoriesByNames
{
    private CategoryRepository $repository;

    public function __construct(
        CategoryRepository $repository,
    )
    {
        $this->repository = $repository;
    }

    public function __invoke(string ...$names): array
    {
        return $this->repository->findByNames(...$names);
    }
}
