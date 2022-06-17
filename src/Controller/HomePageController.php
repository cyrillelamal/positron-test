<?php

namespace App\Controller;

use App\Domain\Category\UseCase\GetTopLevelCategories;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomePageController extends AbstractController
{
    private GetTopLevelCategories $getTopLevelCategories;

    public function __construct(
        GetTopLevelCategories $getTopLevelCategories,
    )
    {
        $this->getTopLevelCategories = $getTopLevelCategories;
    }

    #[Route('/', name: 'index', methods: [Request::METHOD_GET])]
    public function index(): Response
    {
        $categories = ($this->getTopLevelCategories)();

        return $this->render('home_page/index.html.twig', [
            'categories' => $categories,
        ]);
    }
}
