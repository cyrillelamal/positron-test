<?php

namespace App\Domain\Book\UseCase;

use App\Domain\Book\Dto\AddCategoriesDto;
use App\Domain\Category\DefaultCategory\DefaultCategoryProviderInterface;
use App\Entity\Category;
use App\Repository\BookRepository;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AddCategories implements MessageHandlerInterface
{
    private BookRepository $bookRepository;
    private CategoryRepository $categoryRepository;
    private DefaultCategoryProviderInterface $defaultCategoryProvider;

    public function __construct(
        BookRepository                   $bookRepository,
        CategoryRepository               $categoryRepository,
        DefaultCategoryProviderInterface $defaultCategoryProvider,
    )
    {
        $this->bookRepository = $bookRepository;
        $this->categoryRepository = $categoryRepository;
        $this->defaultCategoryProvider = $defaultCategoryProvider;
    }

    public function __invoke(AddCategoriesDto $data): void
    {
        $book = $this->bookRepository->find($data->bookId);

        if (!$book) {
            return;
        }

        $categories = $this->upsert(...$data->categories);
        if (empty($categories)) {
            $categories = $this->defaultCategoryProvider->getDefaultCategories();
        }

        foreach ($categories as $category) {
            $book->addCategory($category);
        }

        $this->bookRepository->add($book, true);
    }

    private function upsert(string ...$names): array
    {
        /** @var Category[] $categories */
        $categories = (new ArrayCollection($names))
            ->filter(fn(string $name) => '' !== trim($name))
            ->map(fn(string $name) => new Category($name))
            ->toArray();

        $this->categoryRepository->upsert(...$categories);

        return $this->reloadCategories(...$categories);
    }

    /**
     * @return Category[]
     */
    private function reloadCategories(Category ...$categories): array
    {
        return $this->categoryRepository->findByNames(
            ...array_map(fn(Category $category) => $category->getName(), $categories)
        );
    }
}
