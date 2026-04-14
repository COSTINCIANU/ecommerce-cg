<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Formulaire de réponse à un message de contact.
 *
 * Utilisé depuis le dashboard admin pour répondre
 * directement à un client par email.
 *
 * @author Gheorghina Costincianu
 */
class ContactResponseFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array<string, mixed> $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('response', TextareaType::class, [
                'label' => 'Votre réponse',
                'attr'  => [
                    'placeholder' => 'Rédigez votre réponse au client...',
                    'rows'        => 8,
                    'class'       => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(message: 'La réponse ne peut pas être vide.'),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer la réponse',
                'attr'  => ['class' => 'btn btn-primary mt-3'],
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}