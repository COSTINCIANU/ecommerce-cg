<?php

namespace App\Controller\Admin;

use App\Entity\Address;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AddressCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Address::class;
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
            TextField::new('name', "Nom de l'adresse"),
            TextField::new('client_name'),
            ChoiceField::new('address_type')->setChoices([
                'Facturation' => 'Facturation',
                'Expédition' => 'Expédition',
            ]),
            //      ChoiceField::new('address_type')->setChoices([
            //     'Billing' => 'Facturation',
            //     'Shipping' => 'Expédition',
            // ]),
            TextField::new('street'),
            TextField::new('code_postal'),
            TextField::new('city'),
            CountryField::new('state'),
            AssociationField::new('user'),
            TextEditorField::new('more_details'),
        ];
    }
   
}
