<?php

namespace App\Controller;

use App\Services\WishListService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;



/**
 * Contrôleur gérant les opérations liées à la liste de souhaits (WishList).
 *
 * Ce contrôleur expose les routes nécessaires pour afficher, ajouter,
 * récupérer et supprimer des produits dans la liste de souhaits de l'utilisateur.
 * Il délègue la logique métier au service {@see WishListService}.
*/
final class WishListController extends AbstractController
{
    /**
     * Injection du service WishList via le constructeur.
     *
     * @param WishListService $wishlistService Service responsable de la logique métier de la WishList.
    */
    public function __construct(   
        private WishListService $wishlistService,
    ) {
       $this->wishlistService = $wishlistService;
    }


    /**
     * Affiche la page principale de la liste de souhaits.
     *
     * Récupère les détails complets de la WishList via le service
     * et les transmet à la vue Twig pour l'affichage.
     *
     * @return Response La vue HTML de la liste de souhaits.
    */
    #[Route('/wishlist', name: 'app_wish_list')]
    public function index(): Response
    {
        // Je récupere les details du wishlist
        $wishlist = $this->wishlistService->getWishListDetails();
        $wishlist_json = json_encode($wishlist);

        
        return $this->render('wish_list/index.html.twig', [
            'controller_name' => 'WishListController',
            'wishlist' =>  $wishlist,
            'wishlist_json' => $wishlist_json, 
        ]);
    }

    /**
     * Ajoute un produit à la liste de souhaits puis redirige vers celle-ci.
     *
     * Reçoit l'identifiant du produit en paramètre de route, délègue l'ajout
     * au service, puis effectue une redirection HTTP vers la page de la WishList.
     * Une alternative JSON est disponible en commentaire pour une utilisation via API.
     *
     * @param string $productId Identifiant unique du produit à ajouter.
     *
     * @return Response Redirection vers la route `app_wish_list`.
    */
    #[Route('/wishlist/add/{productId}', name: 'app_add_to_wishList')]
    public function addToWishList(string $productId): Response
    {
        // J'ajouter au WishList
        $this->wishlistService->addToWishList($productId);

        
        // Je récupere les details du wishList
        $wishlist = $this->wishlistService->getWishListDetails();
        
        // redirection en php 
        // return $this->redirectToRoute("app_wish_list");

        // Et JS je returne le wishList en json 
        return $this->json($wishlist);  
        
    }

    /**
     * Retourne le contenu de la liste de souhaits au format JSON.
     *
     * Utilisé notamment dans un contexte AJAX ou API REST,
     * cette action expose les données de la WishList sérialisées en JSON.
     *
     * @return Response Réponse JSON contenant les détails de la WishList.
    */
    #[Route('/wishlist/get', name: 'app_get_wishList')]
    public function getWishList(): Response
    {
        // Je récupere les details du panier
        $wishlist = $this->wishlistService->getWishListDetails();

        // Je returne le panier en json
        return $this->json($wishlist);
    }


    /**
     * Supprime un produit de la liste de souhaits puis redirige vers celle-ci.
     *
     * Reçoit l'identifiant du produit en paramètre de route, délègue la suppression
     * au service, puis effectue une redirection HTTP vers la page de la WishList.
     * Une alternative JSON est disponible en commentaire pour une utilisation via API.
     *
     * @param string $productId Identifiant unique du produit à supprimer.
     *
     * @return Response Redirection vers la route `app_wish_list`.
    */
    #[Route('/wishlist/remove/{productId}', name: 'app_remove_to_wishList')]
    public function removeToWishList(string $productId): Response
    {
        // Suppresion de la wishlist
        $this->wishlistService->removeToWishList($productId);
        
        // Je récupere les details de la wishlist
        $wishlist = $this->wishlistService->getWishListDetails();

        // Je fait la redirection en php 
        // return $this->redirectToRoute("app_wish_list");

        // Je returne le panier en json
        return $this->json($wishlist);
    }
}
