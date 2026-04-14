<?php

namespace App\Controller\Admin;

use App\Entity\Collection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CollectionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Collection::class;
    }


    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
        ;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('title'),
            TextField::new('description'),
            TextField::new('button_text'),
            TextField::new('button_link'),
            BooleanField::new('isMega'),
            ImageField::new('imageUrl')
                ->setBasePath("assets/images/collections")
                ->setUploadDir("/public/assets/images/collections")
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                // Obligatoir que si on et sur la page de création le mot de passe est requie
                ->setRequired($pageName === Crud::PAGE_NEW)
                ,

        ];
    }
}
