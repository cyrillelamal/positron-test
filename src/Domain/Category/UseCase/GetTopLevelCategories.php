<?php

namespace App\Domain\Category\UseCase;

use App\Domain\Category\Category;
use App\Domain\Category\Repository\CategoryRepository;

class GetTopLevelCategories
{
    private CategoryRepository $repository;

    public function __construct(
        CategoryRepository $repository,
    )
    {
        $this->repository = $repository;
    }

    /**
     * @return Category[]
     */
    public function __invoke(): array
    {
        return $this->repository->findTopLevelCategories();
    }
}
