<?php

namespace App\Domain\Category\UseCase;

use App\Domain\Book\Book;
use App\Domain\Category\Category;
use App\Domain\Category\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class AddDefaultCategory implements LoggerAwareInterface
{
    private LoggerInterface $logger;

    private EntityManagerInterface $entityManager;
    private CategoryRepository $repository;
    private string $defaultCategory;

    public function __construct(
        EntityManagerInterface $entityManager,
        CategoryRepository     $repository,
        string                 $defaultCategory,
    )
    {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->defaultCategory = $defaultCategory;
    }

    public function __invoke(Book $book): void
    {
        $book->addCategory($this->getDefaultCategory());

        $this->entityManager->persist($book);
        $this->entityManager->flush();
    }

    protected function getDefaultCategory(): Category
    {
        $category = $this->repository->findOneBy(['name' => $this->defaultCategory]);

        if (null === $category) {
            $category = new Category($this->defaultCategory);
            $this->entityManager->persist($category);
            $this->logger->info('Created default category.', ['category' => $category]);
        }

        return $category;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}