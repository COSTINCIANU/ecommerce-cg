<?php

namespace App\Controller\Api;

// use PhpParser\Node\Stmt\TryCatch;

use App\Repository\OrderRepository;
use App\Services\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ApiStripeController extends AbstractController
{
    // #[Route('/api/stripe/payment-intent/{orderId}', name: 'app_api_stripe_payment_intent', methods: ['POST'])]
    #[Route('/api/stripe/payment-intent/{orderId}', name: 'app_api_stripe_payment-intent', methods: ['POST'])]
    public function index(int $orderId, 
        StripeService $stripeService,
        EntityManagerInterface $em,
        OrderRepository $orderRepo): Response
    {
        try {
            // Code qui peut potentiellement générer une exception
            $stripeSecretKey = $stripeService->getPrivateKey();

            // Récupère les détails de la commande à partir de l'ID de la commande
            $order = $orderRepo->findOneById($orderId); 
            
            // Vérifiez si la commande existe
            if (!$order) {
                // return $this->json(['error' => 'Commande introuvable'], Response::HTTP_NOT_FOUND);
                return $this->json(['error' => 'Commande introuvable']);
            }
            
            // Initialisez le client Stripe avec ma clé secrète
            $stripe = new StripeClient($stripeSecretKey);

            // Créez une intention de paiement avec le montant et la devise.
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => $order->getOrderCostTtc(), // Calculez le montant total de la commande
                'currency' => 'eur',
               // Dans la dernière version de l'API, la spécification du paramètre `automatic_payment_methods` est facultative car Stripe active sa fonctionnalité par défaut. 
               //Cependant, il est recommandé de l'inclure explicitement pour garantir que les méthodes de paiement automatiques sont activées, surtout si vous utilisez une version plus ancienne de l'API ou si vous souhaitez être explicite dans votre code.
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            // Préparez la réponse à envoyer au client, incluant le client secret de l'intention de paiement.
            $output = [
            'clientSecret' => $paymentIntent->client_secret,  
            ];

            // Stockez l'ID de l'intention de paiement dans la commande pour référence future (par exemple, pour vérifier le paiement après que le client ait complété le processus de paiement).
            $order->setStripeClientSecret($paymentIntent->client_secret); 

            // Enregistrez les modifications dans la base de données.
            $em->persist($order);
            $em->flush();

            return $this->json($output);

        } catch (\Exception $th) {
            // Gérer l'exception, par exemple en enregistrant l'erreur ou en retournant une réponse d'erreur
            // return $this->json(['error' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            return $this->json(['error' => $th->getMessage()]);
        }
    
    }

}
