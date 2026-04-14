<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Formulaire de contact basé exactement sur l'entité Contact.
 *
 * Champs : email, subject, content.
 * Le champ createdAt est géré automatiquement dans le contrôleur.
 *
 * @author Gheorghina Costincianu
 */
class ContactFormType extends AbstractType
{
    /**
     * Construit le formulaire avec les champs de l'entité Contact.
     *
     * @param FormBuilderInterface $builder Constructeur de formulaire Symfony
     * @param array<string, mixed> $options Options du formulaire
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Votre email',
                'attr'  => [
                    'placeholder' => 'votre@email.com',
                    'class'       => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(message: 'Veuillez saisir votre email.'),
                    new Email(message: 'L\'adresse email {{ value }} n\'est pas valide.'),
                ],
            ])
            ->add('subject', TextType::class, [
                'label' => 'Sujet',
                'attr'  => [
                    'placeholder' => 'Objet de votre message',
                    'class'       => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(message: 'Veuillez saisir un sujet.'),
                    new Length(
                        min: 3,
                        max: 255,
                        minMessage: 'Le sujet doit contenir au moins {{ limit }} caractères.',
                        maxMessage: 'Le sujet ne peut pas dépasser {{ limit }} caractères.',
                    ),
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Votre message',
                'attr'  => [
                    'placeholder' => 'Écrivez votre message ici...',
                    'rows'        => 6,
                    'class'       => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(message: 'Le message ne peut pas être vide.'),
                    new Length(
                        min: 10,
                        minMessage: 'Le message doit contenir au moins {{ limit }} caractères.',
                    ),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer le message',
                'attr'  => ['class' => 'btn btn-primary mt-3 w-100'],
            ]);
    }

    /**
     * Configure les options du formulaire.
     *
     * @param OptionsResolver $resolver Résolveur d'options
     *
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}