<?php

namespace App\Controller;


use App\Repository\CarrierRepository;
use App\Services\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{ 
    private CartService $cartService;
    private CarrierRepository $carrierRepo; 

    public function __construct(   
        CartService $cartService,
        CarrierRepository $carrierRepo,
    ) {
        $this->cartService = $cartService;
        $this->carrierRepo = $carrierRepo;
    }



    #[Route('/cart', name: 'app_cart')]
    public function index(): Response
    {
        // Affiche et Je récupere les details du panier
        $cart = $this->cartService->getCartDetails();

        // Je récupére tout les transporteurs
        $carriers = $this->carrierRepo->findAll();

        foreach ($carriers as $key => $carrier) {
            $carriers[$key] = [
                "id" => $carrier->getId(),
                "name" => $carrier->getName(),
                "description" => $carrier->getDescription(),
                "price" => $carrier->getPrice()
            ];
        }


        // Je recupere les données du panier et de Ttansporteur en Json de Cart et Carrier
        $cart_json = json_encode($cart);
        $carriers_json = json_encode($carriers);


        // return $this->json($cart);
        
        return $this->render('cart/index.html.twig', [
            'controller_name' => 'CartController',
            'cart' => $cart,
            'carriers' => $carriers,
            'cart_json' => $cart_json,
            'carriers_json' =>   $carriers_json,
        ]);
    }


    #[Route('/cart/add/{productId}/{count}', name: 'app_add_to_cart')]
    public function addToCart(string $productId, int $count = 1): Response
    {
        // J'ajouter au Panier
        $this->cartService->addToCart($productId, $count);

        
        // Je récupere les details du panier
        $cart = $this->cartService->getCartDetails();
        
        // Et je returne le panier en json
        return $this->json($cart);  
        
        // return $this->redirectToRoute("app_cart");
    }


    /**
     * Suppresion productId du panier 
     */
    #[Route('/cart/remove/{productId}/{count}', name: 'app_remove_to_cart')]
    public function removeToCart(string $productId, int $count = 1): Response
    {
        // Suppresion du Panier
        $this->cartService->removeToCart($productId, $count);
        
        // Je récupere les details du panier
        $cart = $this->cartService->getCartDetails();

        // Je returne le panier en json
        return $this->json($cart);

        // dd($this->cartService->getCartDetails());

        // Et je redirige sur la page Panier
        // return $this->redirectToRoute("app_cart");
    }

    /**
     * Get Cart  
     */
    #[Route('/cart/get', name: 'app_get_cart')]
    public function getCart(): Response
    {
        // Je récupere les details du panier
        $cart = $this->cartService->getCartDetails();

        // Je returne le panier en json
        return $this->json($cart);
    }

    
    /**
     * Mise a jour de transporteur
     */
    #[Route('/cart/carrier', name: 'app_update_cart_carrier', methods: ["POST"])]
    public function updateCartCarrier(Request $req): Response
    {  
        $id = $req->getPayload()->get("carrierId");

        // Je récupere les details du transporteur pour la mise à jour 
        $carrier = $this->carrierRepo->findOneById($id);

        if (!$carrier){
            return $this->redirectToRoute("app_home");
        }

        $this->cartService->update("carrier", [
                "id" => $carrier->getId(),
                "name" => $carrier->getName(),
                "description" => $carrier->getDescription(),
                "price" => $carrier->getPrice()
        ]);

        return $this->redirectToRoute("app_cart");
    }
}
