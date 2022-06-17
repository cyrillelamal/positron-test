<?php

namespace App\Controller\Admin;

use App\Entity\Book;
use App\Entity\Category;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminDashboardController extends AbstractDashboardController
{
    private AdminUrlGenerator $urlGenerator;

    public function __construct(
        AdminUrlGenerator $urlGenerator,
    )
    {
        $this->urlGenerator = $urlGenerator;
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $url = $this->urlGenerator
            ->setController(BookCrudController::class)
            ->generateUrl();

        return  $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle($this->getParameter('app.name'))
            ->setTranslationDomain('admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToCrud('book.nom.pl', 'fa fa-book', Book::class);
        yield MenuItem::linkToCrud('category.nom.pl', 'fa fa-flag', Category::class);
    }
}