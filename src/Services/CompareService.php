<?php 


namespace App\Services;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CompareService {

    protected $session;

    public function __construct(   
        private RequestStack $requestStack,
        private ProductRepository $productRepo,
    ) {
       // J'ai acces a ces methode par tout ou j'ai besoin dans mon CompareService
       $this->session = $requestStack->getSession();
       $this->productRepo = $productRepo;
    }


    /**
     * Le but de cette methode c'est de recupere le panier et le returner 
     */
    public function getCompare() 
    {
       // Si pas de produit dans le panier on returne un tableau vide ici 
       return $this->session->get("compare", []);
    }


    /**
     * Le but de cette methode c'est de mettre a jour le panier
     */
    public function updateCompare($compare) 
    {
       // Si pas de produit dans le panier on returne un tableau vide ici 
       return $this->session->set("compare", $compare);
    }


    /**
     * Le but de cette methode c'est d'ajour au compare
     */
    public function addToCompare($productId) 
    {
        // Je récupere le panier courent
        $compare = $this->getCompare();

        // Je regarde si il y'a des éléments dans le compare avec le productId
        if (!isset($compare[$productId])) {
            // product exist in compare on ajoute 1
            $compare[$productId] = 1;
            $this->updateCompare($compare);
        }

    }

    /**
     * Le but de cette methode c'est de supprimer le product de compare
     */
    public function removeToCompare($productId) 
    {
        // je recuper le panier 
        $compare = $this->getCompare();

        // Je regarde si se defini,  Si non je retire avec unset
        if (isset($compare[$productId])) {
            unset($compare[$productId]);
            // Ee fait la mise a jour du panier 
            $this->updateCompare($compare);
        }

    }


    /**
     * Le but de cette methode c'est de nettoyer le panier 
     */
    public function clearCompare() 
    {
        // Je fait la mise a jour du panier  je met un tableau vide
        $this->updateCompare([]);
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
     *     compare_count: int
     * }
     */
    public function getCompareDetails()
    {
        // Je récupere les données qui a dans le panier
        $compare = $this->getCompare(); 
        $result = [];

        foreach ($compare as $productId => $quantity) {
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
                    ];
            } else {
                // Dans ce cas je retire du compare 
                unset($compare[$productId]);
                // Et je fait la mise a jour
                $this->updateCompare($compare);
            }
        }

        return $result;
    }
}