<?php

namespace App\Domain\Category\UseCase;

use App\Entity\Category;
use App\Repository\BookRepository;
use Doctrine\ORM\Query;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class PaginateCategoryBooks
{
    private PaginatorInterface $paginator;
    private BookRepository $bookRepository;
    private RequestStack $requestStack;

    public function __construct(
        PaginatorInterface $paginator,
        BookRepository     $bookRepository,
        RequestStack       $requestStack,
    )
    {
        $this->paginator = $paginator;
        $this->bookRepository = $bookRepository;
        $this->requestStack = $requestStack;
    }

    public function __invoke(Category|int $category): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->getTarget($category),
            $this->getPage(),
        );
    }

    protected function getTarget(Category|int $category): Query
    {
        return  $this->bookRepository->getQueryForCategoryPagination($category);
    }

    private function getPage(): int
    {
        return (int)$this->getRequest()->query->get('page', 1);
    }

    private function getRequest(): Request
    {
        return $this->requestStack->getMainRequest() ?? Request::createFromGlobals();
    }
}
