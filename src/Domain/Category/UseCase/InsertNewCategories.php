<?php

namespace App\Domain\Category\UseCase;

use App\Domain\Category\Dto\CreateCategoryDto;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;

class InsertNewCategories
{
    private EntityManagerInterface $entityManager;
    private CategoryRepository $repository;

    public function __construct(
        EntityManagerInterface $entityManager,
        CategoryRepository     $repository,
    )
    {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    /**
     * Create only new categories: already existing ones are ignored.
     *
     * @param CreateCategoryDto ...$dtos
     * @return Category[]
     */
    public function __invoke(CreateCategoryDto ...$dtos): array
    {
        $names = array_map(fn(CreateCategoryDto $dto) => trim($dto->name), $dtos);

        $oldNames = $this->repository->findExistingNames(...$names);
        $newNames = array_filter($names, fn(string $name) => '' !== $name && !in_array($name, $oldNames));

        $categories = [];
        foreach ($newNames as $newName) {
            $category = new Category($newName);
            $categories[] = $category;
            $this->entityManager->persist($category);
        }

        $this->entityManager->flush();

        return $categories;
    }
}
