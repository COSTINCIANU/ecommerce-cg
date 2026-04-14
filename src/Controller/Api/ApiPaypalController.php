<?php

namespace App\Controller\Api;

use App\Repository\OrderRepository;
use App\Services\PaypalService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;


final class ApiPaypalController extends AbstractController
{

    // protected mixed $paypal_public_key;
    // protected mixed $paypal_private_key;
    // protected mixed $base;

    public function __construct(
        private PaypalService $paypalService,
        private HttpClientInterface $client,
    )
    {
        // Vous pouvez injecter des services nécessaires pour interagir avec l'API PayPal ici
        $this->paypalService = $paypalService;
        $this->paypal_public_key = $this->paypalService->getPublicKey();
        $this->paypal_private_key = $this->paypalService->getPrivateKey();
        $this->base = $this->paypalService->getBaseUrl();
    }


    // #[Route('/api/paypal/orders', name: 'app_create_paypal', methods: ['POST'])]
    // public function index(Request $req, OrderRepository $orderRepo): Response
    // {
    //     // Décoder le body JSON correctement
    //     $data = json_decode($req->getContent(), true);
    //     $orderId = $data['orderId'] ?? null;

    //     if (!$orderId) {
    //         return $this->json(['error' => 'orderId manquant'], 400);
    //     }

    //     $order = $orderRepo->findOneById($orderId);

    //     if (!$order) {
    //         return $this->json(['error' => 'Commande introuvable'], 404);
    //     }

    //     $result = $this->createOrder($order);
    //     return $this->json($result['jsonResponse']);
    // }


    #[Route('/api/paypal/orders', name: 'app_create_orders', methods: ['POST'])]
    public function index(
        Request $req,
        OrderRepository $orderRepo,
        EntityManagerInterface $em
        ): Response
    {
        $accessToken = $this->generateAccessToken();
        $orderId = $req->getPayload()->get('orderId');
        
        // On recupere de la bdd la commande si existe
        $order = $orderRepo->findOneById($orderId);

        if (!$order) {
            return $this->json(['error' => 'Commande introuvable']);
        }

        $result = $this->createOrder($order);

           if(isset($result['jsonResponse']['id'])) {
                $id = $result['jsonResponse']['id'];
                $order->setPaypalClientSecret($id);
                $em->persist($order);
                $em->flush();
           }
       
            return $this->json($result['jsonResponse']);
    }


    #[Route('/api/orders/{orderID}/capture', name: 'app_capture_paypal', methods: ['POST'])]
    public function capturePayment(
        $orderID,
        Request $req,
        OrderRepository $orderRepo,
        EntityManagerInterface $em)
    {
       try {       
           $result = $this->captureOrder($orderID);
           
           if(isset($result['jsonResponse']['id']) && isset($result['jsonResponse']['status'])) {
                // $id = a L'id du PaypalClientSecret que j'ai stocké dans la bdd lors de la création de la commande, et le status c'est le status du paiement qui peut être 'COMPLETED', 'PENDING', etc.
                $id = $result['jsonResponse']['id'];  
                $status = $result['jsonResponse']['status'];

                
                if ($status === 'COMPLETED') {
                    // On recupere de la bdd la commande si existe
                    $order = $orderRepo->findOneByPaypalClientSecret($id);

                    if($order) {
                        $order->setIsPaid(true);
                        $order->setPaymentMethod('PAYPAL');

                        $em->persist($order);
                        $em->flush();
                    }
                }
            }
            // Vous pouvez ajouter une logique pour mettre à jour le statut de la commande dans votre base de données ici
            return $this->json($result['jsonResponse']);

       } catch (\Exception $error) {
            error_log('Erreur lors de la capture du paiement: ' . $error->getMessage());
            return $this->json(['error' => 'Erreur lors de la capture du paiement.'], 500);
       }
    }

    public function generateAccessToken()
    {
        try {
           if (empty($this->paypal_public_key) || empty($this->paypal_private_key)) {
                throw new \Exception('MISSING_API_CREDENTIALS');
            }

            // Encode the client ID and secret for Basic Authentication
            $auth = base64_encode($this->paypal_public_key . ':' . $this->paypal_private_key);
            
            // Make a POST request to PayPal's OAuth2 token endpoint to get an access token
            $response = $this->client->request(
                'POST', 
                $this->base . '/v1/oauth2/token', 
                [
                    'body' => 'grant_type=client_credentials',
                    'headers' => ['Authorization' => 'Basic '. $auth]
                ]
            );

            // Parse the response to extract the access token
            $data = $response->toArray();
            
            // Return the access token to be used in subsequent API calls
            return $data['access_token'];

        } catch (\Exception $th) {
            return null;
        }
    }


    public function createOrder($order)
    {
        $accessToken = $this->generateAccessToken();
        $url = $this->base . '/v2/checkout/orders';
        $payload = [
            // Order details go here (e.g., intent, purchase_units, etc.)
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => 'EUR',
                        'value' => $order->getOrderCostTtc() / 100,
                    ],
                ],
            ],
        ];

        // Make a POST request to PayPal's Orders API to create an order
        $response = $this->client->request('POST', $url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ],
            'json' => $payload,
        ]);

        return $this->handleResponse($response);
        
    }


    public function captureOrder($orderID)
    {
        $accessToken = $this->generateAccessToken();
        $url = $this->base . '/v2/checkout/orders/' . $orderID . '/capture';

        // Make a POST request to PayPal's Orders API to capture payment for the order
        $response = $this->client->request('POST', $url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]);

        return $this->handleResponse($response);
    }

    public function handleResponse($response)
    {
        try {
            $jsonResponse = json_decode($response->getContent(), true);

            return [
                'jsonResponse' => $jsonResponse,
                'httpStatusCode' => $response->getStatusCode(),
            ];

        } catch (\Exception $e) {
            // Handle exceptions (e.g., log the error, return an error response, etc.)
            $errorMessage = (string) $response->getContent();
            throw new \Exception($errorMessage);
           
        }
    }

}