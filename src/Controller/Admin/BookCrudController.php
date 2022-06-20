<?php

namespace App\Controller\Admin;

use App\Domain\Book\Book;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichImageType;

class BookCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Book::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('book.acc.s')
            ->setEntityLabelInPlural('book.nom.pl')
            ->setPageTitle(Crud::PAGE_EDIT, 'book.edit')
            ->setPageTitle(Crud::PAGE_NEW, 'book.new');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'book.fields.id')
            ->hideOnForm();
        yield TextField::new('title', 'book.fields.title');
        yield TextField::new('isbn', 'book.fields.isbn');
        yield IntegerField::new('pageCount', 'book.fields.page_count');
        yield TextareaField::new('shortDescription', 'book.fields.short_description');
        yield TextareaField::new('longDescription', 'book.fields.long_description');
        yield TextField::new('status', 'book.fields.status'); // TODO: choice field
        yield DateTimeField::new('publishedDate', 'book.fields.published_date');
        yield DateTimeField::new('updatedAt', 'book.fields.updated_at')
            ->hideOnForm()
            ->hideOnIndex();
        yield TextField::new('thumbnailFile', '')
            ->setFormType(VichImageType::class)
            ->onlyOnForms();
        yield ImageField::new('thumbnailUrl', '')
            ->setBasePath($this->getParameter('app.path.book_thumbnails'))
            ->onlyOnIndex();
    }
}
