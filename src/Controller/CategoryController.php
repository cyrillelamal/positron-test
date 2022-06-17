<?php

namespace App\Controller;

use App\Domain\Category\UseCase\PaginateCategoryBooks;
use App\Entity\Category;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/categories', name: 'category_')]
class CategoryController extends AbstractController
{
    private PaginateCategoryBooks $paginateCategoryBooks;

    public function __construct(
        PaginateCategoryBooks $paginateCategoryBooks,
    )
    {
        $this->paginateCategoryBooks = $paginateCategoryBooks;
    }

    #[Route('/{id}', name: 'read', requirements: ['id' => '\d+'], methods: [Request::METHOD_GET])]
    #[ParamConverter(data: 'category', class: Category::class)]
    public function index(Category $category): Response
    {
        $books = ($this->paginateCategoryBooks)($category);

        return $this->render('category/read.html.twig', [
            'category' => $category,
            'books' => $books,
        ]);
    }
}
