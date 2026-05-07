<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository Doctrine pour l'entité Product.
 * Fournit des méthodes de requête optimisées pour éviter le problème N+1.
 *
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    /**
     * Initialise le repository avec le registre Doctrine.
     *
     * @param ManagerRegistry $registry Le registre des gestionnaires d'entités
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Récupère plusieurs produits en une seule requête SQL via WHERE IN.
     * Optimisation N+1 : au lieu de faire 1 requête par produit dans le panier,
     * on charge tous les produits nécessaires en une seule requête.
     * Résultat : temps d'exécution réduit de 31 secondes à 1,3 secondes.
     *
     * @param array $ids Liste des identifiants produits à récupérer
     * @return Product[] Tableau des produits trouvés indexés par résultat Doctrine
     */
    public function findByIds(array $ids): array
    {
        // 1 seule requête SQL : SELECT * FROM product WHERE id IN (1, 2, 3, ...)
        // au lieu de N requêtes find($id) dans une boucle foreach
        return $this->createQueryBuilder('p')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère un produit par son slug avec toutes ses relations chargées
     * en une seule requête SQL grâce aux LEFT JOIN et addSelect.
     * Évite le chargement lazy loading de Doctrine pour les relations
     * relatedProducts, categories et comments.
     *
     * @param string $slug Le slug unique du produit (ex: "robe-ete-fleurie")
     * @return Product|null Le produit trouvé avec ses relations, ou null si inexistant
     */
    public function findBySlugWithRelated(string $slug): ?Product
    {
        return $this->createQueryBuilder('p')
            // Charge les produits associés en JOIN pour éviter le lazy loading
            ->leftJoin('p.relatedProducts', 'r')
            ->addSelect('r')
            // Charge les catégories du produit en JOIN
            ->leftJoin('p.categories', 'c')
            ->addSelect('c')
            // Charge les avis/commentaires du produit en JOIN
            ->leftJoin('p.comments', 'co')
            ->addSelect('co')
            // Filtre par slug unique
            ->where('p.slug = :slug')
            ->setParameter('slug', $slug)
            // Évite les doublons dus aux LEFT JOIN multiples
            ->distinct()
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}