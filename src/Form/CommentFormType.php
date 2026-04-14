<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

/**
 * Formulaire de dépôt d'un avis client sur un produit.
 *
 * Permet à un acheteur vérifié de soumettre une note
 * (1 à 5 étoiles) et un commentaire textuel.
 *
 * @author Gheorghina Costincianu
 */
class CommentFormType extends AbstractType
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
            ->add('rating', ChoiceType::class, [
                'label'    => 'Votre note',
                'choices'  => [
                    '⭐ 1 — Très mauvais'  => 1,
                    '⭐⭐ 2 — Mauvais'       => 2,
                    '⭐⭐⭐ 3 — Correct'      => 3,
                    '⭐⭐⭐⭐ 4 — Bien'        => 4,
                    '⭐⭐⭐⭐⭐ 5 — Excellent' => 5,
                ],
                'expanded'    => true,
                'multiple'    => false,
                'constraints' => [
                    new NotBlank(message: 'Veuillez sélectionner une note.'),
                    new Range(
                        min: 1,
                        max: 5,
                        notInRangeMessage: 'La note doit être entre 1 et 5.'
                    ),
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Votre avis',
                'attr'  => [
                    'placeholder' => 'Partagez votre expérience...',
                    'rows'        => 4,
                    'class'       => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(message: 'Le commentaire ne peut pas être vide.'),
                    new Length(
                        min: 10,
                        max: 1000,
                        minMessage: 'Minimum {{ limit }} caractères.',
                        maxMessage: 'Maximum {{ limit }} caractères.',
                    ),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Publier mon avis',
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
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}