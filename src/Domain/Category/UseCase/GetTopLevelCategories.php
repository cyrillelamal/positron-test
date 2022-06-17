<?php

namespace App\Domain\Category\UseCase;

use App\Entity\Category;
use App\Repository\CategoryRepository;

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
