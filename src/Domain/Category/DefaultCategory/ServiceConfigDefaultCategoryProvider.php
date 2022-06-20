<?php

namespace App\Domain\Category\DefaultCategory;

use App\Domain\Category\Category;
use App\Domain\Category\Repository\CategoryRepository;

class ServiceConfigDefaultCategoryProvider implements DefaultCategoryProviderInterface
{
    private string $defaultCategoryName;

    private ?Category $category = null;
    private CategoryRepository $repository;

    public function __construct(
        string             $defaultCategoryName,
        CategoryRepository $repository,
    )
    {
        $this->defaultCategoryName = $defaultCategoryName;
        $this->repository = $repository;
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultCategories(): array
    {
        if ($this->hasCachedCategory()) {
            return [$this->getCachedCategory()];
        }

        $category = $this->category = $this->loadCategoryFromRepository();

        if (null === $category) {
            $category = $this->category = new Category($this->defaultCategoryName);
            $this->repository->add($category, true);
        }

        return [$category];
    }

    private function hasCachedCategory(): bool
    {
        return null !== $this->category;
    }

    private function getCachedCategory(): ?Category
    {
        return $this->category;
    }

    private function loadCategoryFromRepository(): ?Category
    {
        return $this->repository->findOneBy(['name' => $this->defaultCategoryName]);
    }
}
