<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    //    /**
    //     * @return Order[] Returns an array of Order objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('o.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Order
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }



    /**
     * Vérifie si un utilisateur possède au moins une commande payée.
     *
     * Utilisé pour autoriser ou non le dépôt d'un avis produit.
     * Une commande est considérée payée quand is_paid = 1.
     *
     * @param User $user L'utilisateur à vérifier
     *
     * @return bool True si au moins une commande payée existe
     */
    public function hasUserPaidOrder(User $user): bool
    {
        return (bool) $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->where('o.user = :user')
            ->andWhere('o.isPaid = true')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }




    /**
     * Récupère toutes les commandes d'un utilisateur.
     *
     * Triées de la plus récente à la plus ancienne.
     *
     * @param User $user L'utilisateur connecté
     *
     * @return Order[] Liste des commandes
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('o')
            ->where('o.user = :user')
            ->setParameter('user', $user)
            ->orderBy('o.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
