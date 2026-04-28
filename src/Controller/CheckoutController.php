<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Repository\AddressRepository;
use App\Repository\OrderRepository;
use App\Services\CartService;
use App\Services\PaypalService;
use App\Services\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;

final class CheckoutController extends AbstractController
{
    private SessionInterface $session;
    /**
     * Initialise le service avec les dépendances injectées.
     *
     * Note : l'accès à la session dans le constructeur est déconseillé
     * car elle peut ne pas être encore accessible à ce stade.
     * On stocke donc uniquement la référence pour un accès ultérieur.
     * @param CartService CartService Service responsable de la logique métier de la WishList.
     * @param RequestStack      $requestStack      Pile de requêtes Symfony (accès à la session)
     * 
     * @author Gheorghina Costincianu
     * @version 1.0
    */
    public function __construct(   
        private CartService $cartService,
        private RequestStack $requestStack,
        private EntityManagerInterface $em,
        private OrderRepository $orderRepo
       
    ) {
        // J'ai acces a ces methode par tout ou j'ai besoin dans mon CartService
        $this->cartService = $cartService;
        $this->session = $requestStack->getSession();
        $this->em = $em;
        $this->orderRepo = $orderRepo;
    }



    #[Route('/checkout', name: 'app_checkout')]
    public function index(
        AddressRepository $addressRepository,
        StripeService $stripeService,
        PaypalService $paypalService
        ): Response
    {

        // Je récupere les details du panier
        $cart = $this->cartService->getCartDetails();

        if(!count($cart["items"])) {
            return $this->redirectToRoute('app_home');
        }

        $user = $this->getUser();

        if (!$user) {
            $this->session->set("next", "app_checkout");
            return $this->redirectToRoute('app_login');
        }

         $addresses = $addressRepository->findByUser($user);


        // Je recupere les données du panier en Json 
        $cart_json = json_encode($cart);


        $orderId = $this->createOrder($cart);
        // dd($orderId);

        // Je recupere la clé publique de Stripe et de Paypal pour le front
        $stripe_public_key = $stripeService->getPublicKey();
        $paypal_public_key = $paypalService->getPublicKey();


        // Je rend la page de checkout et je lui passe les details du panier, les clés publiques et les adresses de l'utilisateur
        return $this->render('checkout/index.html.twig', [
            'controller_name' => 'CheckoutController',
            'cart' => $cart,
            'orderId' => $orderId,
            'cart_jason' => $cart_json,
            'stripe_public_key' => $stripe_public_key,
            'paypal_public_key' => $paypal_public_key,
            'addresses' => $addresses,
        ]);
    }


    #[Route('/stripe/payment/success', name: 'app_stripe_payment_success')]
    public function paymentSuccess(Request $req, 
        OrderRepository $orderRepo, 
        EntityManagerInterface $em,
        MailerInterface $mailer
        ): Response 
    { 

        // Récupérer le client secret de l'intention de paiement à partir de la requête
        $stripeClientSecret = $req->query->get("payment_intent_client_secret");

        // Rechercher la commande associée à ce client secret
        $order = $orderRepo->findOneByStripeClientSecret($stripeClientSecret);

                if (!$order) {
                    // Si aucune commande n'est trouvée, rediriger vers la page d'erreur
                    return $this->redirectToRoute('app_error');
                }

                // Si la commande est trouvée, mettre à jour son statut pour indiquer que le paiement a réussi
                // Vider le panier après le paiement réussi
                $this->cartService->update('cart', []);

                // dd($order);
                $order->setIsPaid(true);
                $order->setPaymentMethod('STRIPE');
                $order->setStatus('Payée'); // ajoute cette ligne

            $em->persist($order);
            $em->flush();

            // ← ajoute ici après le flush
            $email = (new TemplatedEmail())
                ->from(new Address('costincianu.gheorghina@gmail.com', 'C.G Boutique'))
                ->to($order->getUser()->getEmail())
                ->subject('Confirmation de votre commande #' . $order->getId())
                ->htmlTemplate('emails/confirmation_commande.html.twig')
                ->context(['order' => $order]);
            $mailer->send($email);

            // return $this->render('payment/index.html.twig', [...]);

            // Mettre à jour le statut de la commande pour indiquer que le paiement a réussi
            return $this->render('payment/index.html.twig', [
                'controller_name' => 'PaymentController',
               
            ]);
    }
        
   

    #[Route('/paypal/payment/success', name: 'app_paypal_payment_success')]
    public function paypalPaymentSuccess(Request $req, 
        OrderRepository $orderRepo, 
        EntityManagerInterface $em,
        MailerInterface $mailer
        ): Response 
    { 
         $user = $this->getUser();
         
        // Récupère la dernière commande PayPal de l'utilisateur
        $order = $orderRepo->findOneBy(
            ['user' => $user, 'isPaid' => true, 'paymentMethod' => 'PAYPAL'],
            ['id' => 'DESC']
        );

        // Vider le panier après paiement PayPal
        $this->cartService->clearCart();

        // Envoyer email confirmation si commande trouvée
        if ($order) {
            $email = (new TemplatedEmail())
                ->from(new Address('costincianu.gheorghina@gmail.com', 'C.G Boutique'))
                ->to($order->getUser()->getEmail())
                ->subject('Confirmation de votre commande #' . $order->getId())
                ->htmlTemplate('emails/confirmation_commande.html.twig')
                ->context(['order' => $order]);
            $mailer->send($email);
        }

        return $this->render('payment/index.html.twig', [
            'controller_name' => 'PaymentController',
        ]);
    }
        
   




    /**
     * Crée une commande dans la base de données à partir des détails du panier.
     * @param array $cart Les détails du panier, incluant les produits, quantités, prix, etc.
     * @return int L'ID de la commande créée.
     * @author Gheorghina Costincianu
     * @version 1.0
     * @throws \Exception Si une erreur survient lors de la création de la commande.
     * Note : Je m'assure que les entités Order et OrderDetails sont correctement définies et que les relations entre elles sont configurées dans ma base de données.
     * De plus, cette méthode suppose que les détails du panier sont structurés de manière spécifique (par exemple, $cart['items'] contient les produits et leurs détails). 
     * Structure réelle  de panier.
     * Enfin, cette méthode utilise le service EntityManager pour persister les données dans la base de données, 
     * Je m'assure que l'injection de dépendance est correctement configurée pour que $this->em soit disponible et fonctionnel.
     */
    public function createOrder($cart){   
        // Récupère l'utilisateur actuellement connecté
        $user = $this->getUser();

        $order = $this->orderRepo->findOneBy([
            "client_name" => $user->getFullName(),
            "order_cost_ht" => $cart['sub_total_ht'],
            "taxe" => $cart['taxe'],
            "order_cost_ttc" => $cart['sub_total_with_carrier'],
            "carrier_name" => $cart['carrier']['name'],
            "carrier_price" => $cart['carrier']['price'],
            "carrier_id" => $cart['carrier']['id'],
            "quantity" => $cart['quantity'],
            "isPaid" => false
        ]);

        if(!$order) {
            // Ici, vous pouvez implémenter la logique pour créer une commande dans votre base de données
            // et retourner l'ID de la commande créée. Par exemple 
            $order = new Order();
        }

        $order->setClientName($user->getFullName())
                ->setBillingAddress("")
                ->setShippingAddress("")
                ->setQuantity($cart['quantity'])
                ->setOrderCostHt($cart['sub_total_ht'])
                ->setOrderCostTtc($cart['sub_total_with_carrier'])
                ->setTaxe($cart['taxe'])
                ->setCarrierName($cart['carrier']['name'])
                ->setCarrierPrice($cart['carrier']['price'])
                ->setCarrierId($cart['carrier']['id'])
                ->setIsPaid(false)
                ->setStatus("En cours de paiement")
                ->setUser($user) 
               ;
        // ... (autres propriétés de la commande)

        $this->em->persist($order);

        // ← AJOUT de condition ici 
        if($order->getOrderDetails()->isEmpty()) {
            foreach ($cart['items'] as $key => $item) {
                $product = $item['product'];

                $orderDetails = new OrderDetails();

                $orderDetails->setProductName($product['name'])
                    ->setProductDescription($product['description'])
                    ->setProductSoldePrice($product['soldePrice'])
                    ->setProductRegularPrice($product['regularPrice'])
                    ->setQuantity($item['quantity'])
                    ->setSubTotal($item['sub_total'])
                    ->setTaxe($item['taxe'])
                    ->setMyOrder($order);

                $this->em->persist($orderDetails);
            }
        }

        $this->em->flush();

        return $order->getId();
    }
}