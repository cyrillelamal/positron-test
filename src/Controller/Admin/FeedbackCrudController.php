<?php

namespace App\Controller\Admin;

use App\Domain\Feedback\Feedback;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Contracts\Translation\TranslatorInterface;

class FeedbackCrudController extends AbstractCrudController
{
    private TranslatorInterface $translator;

    public function __construct(
        TranslatorInterface $translator,
    )
    {
        $this->translator = $translator;
    }

    public static function getEntityFqcn(): string
    {
        return Feedback::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Action::INDEX, Action::DETAIL)
            ->disable(Action::NEW, Action::EDIT);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular('feedback.nom.s')
            ->setEntityLabelInPlural('feedback.nom.pl')
            ->setPageTitle(Crud::PAGE_INDEX, $this->translator->trans('feedback.index', domain: 'admin'))
            ->setPageTitle(Crud::PAGE_DETAIL, $this->translator->trans('feedback.detail', domain: 'admin'))
            ->showEntityActionsInlined();
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'feedback.fields.id');
        yield EmailField::new('email', 'feedback.fields.email');
        yield TextField::new('name', 'feedback.fields.name');
        yield TextField::new('phoneNumber', 'feedback.fields.phone_number');
        yield TextareaField::new('message', 'feedback.fields.message');
    }
}
