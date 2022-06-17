<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('category.acc.s')
            ->setEntityLabelInPlural('category.nom.pl')
            ->setPageTitle(Crud::PAGE_EDIT, 'category.edit')
            ->setPageTitle(Crud::PAGE_NEW, 'category.new');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'category.fields.id')
            ->hideOnForm();
        yield TextField::new('name', 'category.fields.name');
    }
}
