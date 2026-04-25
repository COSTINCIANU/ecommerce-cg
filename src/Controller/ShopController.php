<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ShopController extends AbstractController
{
    #[Route('/shop-list', name: 'app_shop_list')]
    public function index(
        Request $request,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository
    ): Response {
        $categorySlug = $request->query->get('categorie');
        $tri = $request->query->get('tri');
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 12;

        // Récupérer tous les produits disponibles
        $qb = $productRepository->createQueryBuilder('p')
            // ->where('p.isAvailable = true');
            // Par :
        ->where('p.isAvailable = true OR p.isAvailable IS NULL');

        // Filtre par catégorie
        if ($categorySlug) {
            $qb->join('p.categories', 'c')
               ->andWhere('c.slug = :slug')
               ->setParameter('slug', $categorySlug);
        }

        // Tri
        match ($tri) {
            'nouveautes'    => $qb->orderBy('p.created_at', 'DESC'),
            'prix-asc'      => $qb->orderBy('p.solde_price', 'ASC'),
            'prix-desc'     => $qb->orderBy('p.solde_price', 'DESC'),
            default         => $qb->orderBy('p.created_at', 'DESC'),
        };

        // Pagination
        $total = count((clone $qb)->getQuery()->getResult());
        $products = $qb->setFirstResult(($page - 1) * $limit)
                       ->setMaxResults($limit)
                       ->getQuery()
                       ->getResult();

        $categories = $categoryRepository->findAll();

        return $this->render('shop/shop-list.html.twig', [
            'products'      => $products,
            'categories'    => $categories,
            'currentCat'    => $categorySlug,
            'currentTri'    => $tri,
            'currentPage'   => $page,
            'totalPages'    => ceil($total / $limit),
            'total'         => $total,
        ]);
    }
}