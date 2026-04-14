<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    public function __construct(
        public UserPasswordHasherInterface $userPasswordHasher
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
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
        // https://symfony.com/bundles/EasyAdminBundle/current/crud.html#crud-pages
        // https://symfony.com/bundles/EasyAdminBundle/current/fields.html
        // https://symfony.com/bundles/EasyAdminBundle/current/fields.html#field-layout
        return [
            IdField::new('id')->hideOnForm(),
            ChoiceField::new('Civility')->setChoices([
                'Monsieur' => 'Mr',
                'Madame' => 'Mme',
                'Mademoiselle' => 'Mlle',
            ]),
            TextField::new('full_name'),
            EmailField::new('email'),
            AssociationField::new('addresses'),
            TextField::new('password')
            ->setFormType(RepeatedType::class)
            ->setFormTypeOptions([
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Password',
                    'row_attr' => [
                        'class' => "col-md-6 col-xxl-5"
                    ]
                ],
                'second_options' => [
                    'label' => 'Confirm Password',
                    'row_attr' => [
                        'class' => "col-md-6 col-xxl-5"
                    ]
                ],
                'mapped' => false, 
            ])
            // Obligatoir que si on et sur la page de création le mot de passe est requie
            ->setRequired($pageName === Crud::PAGE_NEW)
            // ->hideOnIndex()->hideWhenUpdating(),
            ->onlyOnForms(),
        ];
    }
    
    /**
     * On créer le formBuilder
     */
    public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        // On créer le form builder 
        $formBuilder = parent::createNewFormBuilder($entityDto, $formOptions, $context);

        return $this->addPasswordEventListener($formBuilder);
    }

     /**
     * On fait l'edition du formBuilder
     */
    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        // On créer le form builder 
        $formBuilder = parent::createEditFormBuilder($entityDto, $formOptions, $context);

        return $this->addPasswordEventListener($formBuilder);
    }

     /**
     * On capte et on ecoute l'evenement
     */
    public function addPasswordEventListener(FormBuilderInterface $formBuilder){
        return $formBuilder->addEventListener(FormEvents::POST_SUBMIT, $this->hashPassword());
    }

    public function hashPassword(){
        return function($event){
            $form = $event->getForm();
            if(!$form->isValid()){
                return;
            }

            $password = $form->get('password')->getData();

            if($password == null){
                return;
            }

            // $hash = $this->userPasswordHasher->hashPassword($this->getUser(), $password);
            $hash = $this->userPasswordHasher->hashPassword($form->getData(), $password);
            $form->getData()->setPassword($hash);
        };
    }
}