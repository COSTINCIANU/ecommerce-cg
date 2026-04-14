<?php

namespace App\Controller;

use App\Services\CompareService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CompareController extends AbstractController
{
    public function __construct(   
        private CompareService $compareService,
    ) {
       $this->compareService = $compareService;
    }


    #[Route('/compare', name: 'app_compare')]
    public function index(): Response
    {
        // Je récupere les details du panier
        $compare = $this->compareService->getCompareDetails();

        // Je recupere les données en Json 
        $compare_json = json_encode($compare);

        // return $this->json($compare);
        
        return $this->render('compare/index.html.twig', [
            'controller_name' => 'CompareController',
            // Et je affiche la templetes
            'compare' => $compare,
            'compare_json' => $compare_json,
        ]);
    }

    /**
     * Permet de ajouter
     */
    #[Route('/compare/add/{productId}', name: 'app_add_to_compare')]
    public function addToCompare(string $productId): Response
    {
        // J'ajouter au Compare
        $this->compareService->addToCompare($productId);

        
        // Je récupere les details du compare
        $compare = $this->compareService->getCompareDetails();
        
        // redirection en php 
        // return $this->redirectToRoute("app_compare");

        // Et JS je returne le compare en json 
        return $this->json($compare);  
        
    }

    /**
     * Permet de récupérer
     */
    #[Route('/compare/get', name: 'app_get_compare')]
    public function getCompare(): Response
    {
        // Je récupere les details du panier
        $compare = $this->compareService->getCompareDetails();

        // Je returne le panier en json
        return $this->json($compare);
    }


    /**
     * Suppresion productId du panier 
     */
    #[Route('/compare/remove/{productId}', name: 'app_remove_to_compare')]
    public function removeToCompare(string $productId): Response
    {
        // Suppresion du Panier
        $this->compareService->removeToCompare($productId);
        
        // Je récupere les details du panier
        $compare = $this->compareService->getCompareDetails();

        // Je returne le panier en json
        return $this->json($compare);

        // dd($this->compareService->getCompareDetails());

        // Et je redirige sur la page Panier
        // return $this->redirectToRoute("app_compare");
    }
}
