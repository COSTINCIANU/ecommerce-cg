<?php 


namespace App\Services;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class WishListService {

    private SessionInterface $session; 

    public function __construct(   
        private RequestStack $requestStack,
        private ProductRepository $productRepo,
    ) {
       // J'ai acces a ces methode par tout ou j'ai besoin dans mon WishListService
       $this->session = $requestStack->getSession();
       $this->productRepo = $productRepo;
    }


    /**
     * Le but de cette methode c'est de recupere le panier et le returner 
     */
    public function getWishList() 
    {
       // Si pas de produit dans le panier on returne un tableau vide ici 
       return $this->session->get("wishList", []);
    }


    /**
     * Le but de cette methode c'est de mettre a jour le panier
     */
    public function updateWishList(array $wishList) 
    {
       // Si pas de produit dans le panier on returne un tableau vide ici 
       return $this->session->set("wishList", $wishList);
    }


    /**
     * Le but de cette methode c'est d'ajour au wishList
     */
    public function addToWishList(int $productId) 
    {
        // Je récupere le panier courent
        $wishList = $this->getWishList();

        // Je regarde si il y'a des éléments dans le wishList avec le productId
        if(!isset($wishList[$productId])) {
            // product exist in wishList on ajoute 1
            $wishList[$productId] = 1;
            $this->updateWishList($wishList);
        }

    }

    /**
     * Le but de cette methode c'est de supprimer le product de wishList
     */
    public function removeToWishList(int $productId) 
    {
        // je recuper le panier 
        $wishList = $this->getWishList();

        // Je regarde si se defini 
        if(isset($wishList[$productId])) {
            // Si non je retire 
            unset($wishList[$productId]);
            // Ee fait la mise a jour du panier 
            $this->updateWishList($wishList);
        }

    }


    /**
     * Le but de cette methode c'est de nettoyer le panier 
     */
    public function clearWishList() 
    {
        // Je fait la mise a jour du panier  je met un tableau vide
        $this->updateWishList([]);
    }

       
    /**
     * Récupère les détails complets du panier.
     *
     * Cette méthode parcourt les produits stockés dans le panier (session),
     * récupère leurs informations depuis la base de données et calcule :
     * - le sous-total par produit
     * - le total global du panier
     * - le nombre total d'articles
     *
     * Si un produit n'existe plus en base, il est supprimé du panier.
     *
     * @return array{
     *     items: array<int, array{
     *         product: array{
     *             id: int,
     *             name: string,
     *             slug: string,
     *             imageUrls: array<int, string>,
     *             soldePrice: int,
     *             regularPrice: int
     *         },
     *         quantity: int,
     *         sub_total: int
     *     }>,
     *     sub_total: int,
     *     wishList_count: int
     * }
     */
    public function getWishListDetails()
    {
        // Je récupere les données qui a dans le panier
        $wishList = $this->getWishList(); 
        $result = [];

        foreach ($wishList as $productId => $quantity) {
            $product = $this->productRepo->find($productId);
            // Si je ne touve le produit dans ce cas l'id ne pas correct 
            if($product) {
                // je returne le produit 
                $result[] = [ 
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'slug' => $product->getSlug(),
                    'imageUrls' => $product->getImageUrls(),
                    'soldePrice' => $product->getSoldePrice(),
                    'regularPrice' => $product->getRegularPrice(),
                    'stock' => $product->getStock(),
                    ];
            }else {
                // Dans ce cas je retire du wishList 
                unset($wishList[$productId]);
                // Et je fait la mise a jour
                $this->updateWishList($wishList);
            }
        }

        return $result;
    }
}