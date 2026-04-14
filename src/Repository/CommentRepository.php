<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /**
     * Vérifie si un utilisateur a déjà laissé un avis sur un produit.
     *
     * @param User    $user    L'utilisateur connecté
     * @param Product $product Le produit concerné
     *
     * @return bool True si un avis existe déjà
     */
    public function hasUserAlreadyCommented(User $user, Product $product): bool
    {
        return (bool) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.author = :user')
            ->andWhere('c.product = :product')
            ->setParameter('user', $user)
            ->setParameter('product', $product)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Récupère tous les avis publiés d'un produit.
     *
     * @param Product $product Le produit concerné
     *
     * @return Comment[]
     */
    public function findPublishedByProduct(Product $product): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.product = :product')
            ->andWhere('c.isPublished = true')
            ->setParameter('product', $product)
            ->orderBy('c.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Calcule la note moyenne d'un produit.
     *
     * @param Product $product Le produit concerné
     *
     * @return float Note moyenne arrondie à 1 décimale
     */
    public function getAverageRating(Product $product): float
    {
        $result = $this->createQueryBuilder('c')
            ->select('AVG(c.rating) as avg_rating')
            ->where('c.product = :product')
            ->andWhere('c.isPublished = true')
            ->setParameter('product', $product)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? round((float) $result, 1) : 0.0;
    }
}