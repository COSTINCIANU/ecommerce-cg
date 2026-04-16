<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findByIds(array $ids): array
    {
        // charger tous les produits en 1 seule requête
        return $this->createQueryBuilder('p')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }
    
    public function findBySlugWithRelated(string $slug): ?Product
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.relatedProducts', 'r')
            ->addSelect('r')                    // charge tout en 1 seule requête
            ->leftJoin('p.categories', 'c')
            ->addSelect('c')
            ->leftJoin('p.comments', 'co')
            ->addSelect('co')
            ->where('p.slug = :slug')
            ->setParameter('slug', $slug)
            ->distinct()  // ← ajouter cette ligne
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
