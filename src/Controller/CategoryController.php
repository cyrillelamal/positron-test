<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/categories', name: 'category_')]
class CategoryController extends AbstractController
{
    public function __construct(
        CategoryRepository $repository,
    )
    {
    }

    #[Route('/{id}', name: 'read', requirements: ['id' => '\d+'])]
    public function index(int $id): Response
    {
        throw new \LogicException('TODO: implement');
    }
}
