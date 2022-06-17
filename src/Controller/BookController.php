<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/books', name: 'book_')]
class BookController extends AbstractController
{
    private BookRepository $repository;

    public function __construct(
        BookRepository $repository,
    )
    {
        $this->repository = $repository;
    }

    #[Route('/{id}', name: 'read', requirements: ['id' => '\d+'])]
//    #[ParamConverter(data: 'book', class: Book::class)]
    public function read(int $id): Response
    {
        $book = $this->repository->findByIdJoinCategoriesAndAuthors($id) or throw new NotFoundHttpException();

        // TODO: paginate
        $otherBooksFromCategory = $this->repository->findBooksInSameCategory($book);

        return $this->render('book/read.html.twig', [
            'book' => $book,
            'other_books_from_category' => $otherBooksFromCategory,
        ]);
    }
}
