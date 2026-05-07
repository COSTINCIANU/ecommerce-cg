<?php 

// declare(strict_types=1); 
namespace App\Services;

use App\Repository\CarrierRepository;
use App\Repository\ProductRepository;
use App\Repository\SettingRepository;
use Symfony\Component\HttpFoundation\RequestStack;




/**
 * Service de gestion du panier d'achat.
 *
 * Responsable de toutes les opérations liées au panier :
 * ajout, suppression, mise à jour des quantités, calcul des totaux
 * (HT, TVA, TTC) et gestion du transporteur sélectionné.
 *
 * Les données du panier sont stockées en session sous la clé "cart"
 * sous la forme : [productId => quantity].
 *
 * @author Gheorghina Costincianu
*/
class CartService {

    /**
     * Instance de la session Symfony utilisée pour persister le panier.
    */
    protected $session; 

    /**
     * Initialise le service avec les dépendances injectées.
     *
     * Note : l'accès à la session dans le constructeur est déconseillé
     * car elle peut ne pas être encore accessible à ce stade.
     * On stocke donc uniquement la référence pour un accès ultérieur.
     *
     * @param RequestStack      $requestStack      Pile de requêtes Symfony (accès à la session)
     * @param ProductRepository $productRepo       Repository des produits
     * @param CarrierRepository $carrierRepo Repository des transporteurs
     * @param SettingRepository $settingRepo       Repository des paramètres du site
    */
    public function __construct(   
        private RequestStack $requestStack,
        private ProductRepository $productRepo,
        private CarrierRepository $carrierRepo,
        private SettingRepository $settingRepo,
    ) {
       // J'ai acces a ces methode par tout ou j'ai besoin dans mon CartService
       $this->session = $requestStack->getSession();
    }


    /**
     * Récupère une valeur stockée en session par sa clé.
     *
     * Retourne un tableau vide par défaut si la clé n'existe pas,
     * ce qui évite les erreurs de type sur les appels suivants.
     *
     * @param string $key Clé de session (ex: "cart", "carrier")
     *
     * @return array<int|string, mixed> Données stockées en session
    */
    public function get($key)
    {
       // Si pas de produit dans le panier on returne un tableau vide ici 
       return $this->session->get($key, []);
    }


    /**
     * Met à jour une valeur en session.
     *
     * @param string              $key  Clé de session à mettre à jour
     * @param array<mixed, mixed> $cart Données à stocker
     *
     * @return void
     */
    public function update($key, $cart)
    {
       // Si pas de produit dans le panier on returne un tableau vide ici 
       return $this->session->set($key, $cart);
        // $this->session->set($key, $cart);
    }


    /**
     * Ajoute un produit au panier ou incrémente sa quantité.
     *
     * Si le produit est déjà présent dans le panier, sa quantité
     * est augmentée de $count. Sinon, il est ajouté avec la quantité $count.
     *
     * @param int $productId Identifiant du produit à ajouter
     * @param int $count     Quantité à ajouter (1 par défaut)
     *
     * @return void
     */
    public function addToCart($productId, $count = 1)
    {
        // Je récupere le panier courent
        $cart = $this->get('cart');

        // Je regarde si il y'a des éléments dans le panier
        // avec la clé productId
        if (!empty($cart[$productId])) {
            // product exist in cart on ajoute par deçu
            $cart[$productId] += $count;
        } else {
            // product not exist on le créé le produit
            $cart[$productId] = $count;
        }

        $this->update("cart", $cart);
    }


    /**
     * Retire une quantité d'un produit du panier.
     *
     * Si la quantité restante après suppression est inférieure ou égale
     * à zéro, le produit est complètement retiré du panier.
     *
     * @param int $productId Identifiant du produit à retirer
     * @param int $count     Quantité à retirer (1 par défaut)
     *
     * @return void
    */
    public function removeToCart($productId, $count = 1)
    {
        // je recuper le panier 
        $cart = $this->get('cart');

        // Je regarde si il y'a des élément dans le panier 
        if (isset($cart[$productId])) {
            // Si supperieur la quantite qui y'a déjà dans le panier 
            if ($cart[$productId] <= $count){
                // Je rétire le produit du panier 
                unset($cart[$productId]);
            } else {
                // Je retire la quantite du panier
                $cart[$productId] -= $count;
            }
            // Je fait la mise a jour du panier 
            $this->update("cart", $cart);
        }

    }

    /**
     * Vide entièrement le panier.
     *
     * Appelé typiquement après la validation d'une commande
     * ou à la demande explicite de l'utilisateur.
     *
     * @return void
    */
    public function clearCart()
    {
        // Je fait la mise a jour du panier  je met un tableau vide
        $this->update("cart", []);
    }


    /**
     * Met à jour le transporteur sélectionné en session.
     *
     * Le transporteur est stocké sous forme de tableau associatif
     * contenant : id, name, description, price.
     * Exemple de transporteurs : UPS, Colissimo, Chronopost.
     *
     * @param array{id: int, name: string, description: string|null, price: int} $carrier
     *        Données du transporteur sélectionné
     *
     * @return void
     */
    public function updateCarrier($carrier)
    {
        // Mise à jour de transporteur
        $this->update("carrier", $carrier);
    }
       


    /**
     * Calcule et retourne le détail complet du panier.
     *
     * Pour chaque produit en session, récupère les informations depuis
     * la base de données et calcule les montants HT, TVA et TTC.
     * Applique le taux de TVA configuré dans les paramètres du site (Setting).
     * Ajoute les frais de port du transporteur sélectionné (ou le
     * premier transporteur disponible par défaut).
     * Si un produit n'existe plus en BDD, il est automatiquement
     * retiré du panier.
     *
     * @return array{
     *   items: array<int, array{
     *     product: array{
     *       id: int,
     *       name: string,
     *       slug: string,
     *       imageUrls: mixed,
     *       description: string|null,
     *       soldePrice: int,
     *       regularPrice: int
     *     },
     *     quantity: int,
     *     sub_total_ht: int,
     *     taxe: int,
     *     sub_total: int
     *   }>,
     *   sub_total: int,
     *   sub_total_ht: int,
     *   taxe: int,
     *   cart_count: int,
     *   quantity: int,
     *   carrier: array<string, mixed>,
     *   sub_total_with_carrier: int
     * }
     */
    public function getCartDetails()
    {
        // Je récupere les données qui a dans le panier
        $cart = $this->get('cart');    
        // On recupre les details dans un tableau
        $result = [
            'items' => [],
            'sub_total' => 0,
            'cart_count' => 0, // Par defaut je le mets a zero
            'quantity' => 0, // Par defaut je le mets a zero
        ];
        $sub_total = 0;
        $taxe_rate = 0;

        $setting = $this->settingRepo->findOneBy(["website_name" => "C.G"]);
        if($setting){
            $taxe_rate = $setting->getTaxeRate()/100;
        }


        // Extraire les IDs du panier pour la requête optimisée
        $productIds = array_keys($cart);
        // 1 seule requête pour tous les produits du panier N
        $products = $this->productRepo->findByIds($productIds);

        // Indexer par id pour accès rapide
        $productsById = [];
        foreach ($products as $p) {
            $productsById[$p->getId()] = $p;
        }
        foreach ($cart as $productId => $quantity) {
            $product = $productsById[$productId] ?? null;            
            if($product) {
                $current_sub_total = $product->getSoldePrice()*$quantity;
                $sub_total += $current_sub_total;
                $result['items'] [] = [
                    'product' => [ 
                        'id' => $product->getId(),
                        'name' => $product->getName(),
                        'description' => $product->getDescription(),
                        'slug' => $product->getSlug(),
                        'imageUrls' => $product->getImageUrls(),
                        'soldePrice' => $product->getSoldePrice(),
                        'regularPrice' => $product->getRegularPrice(),
                    ],
                    'quantity' => $quantity,
                    'sub_total_ht' => round($current_sub_total/(1 + $taxe_rate)),
                    'taxe' => round($taxe_rate * $current_sub_total/(1 + $taxe_rate)),
                    'sub_total' => $current_sub_total,
                ];
                $result['sub_total'] = $sub_total;
                $result['sub_total_ht'] = round($sub_total/(1 + $taxe_rate));
                $result['taxe'] = round($taxe_rate * $result['sub_total_ht']);
                $result['cart_count'] += $quantity; 
                $result['quantity'] += $quantity; 
            
            } else {
                unset($cart[$productId]);
                $this->update("cart", $cart);
            }
        }

        // Je recupere les transporteur 
        $carrier = $this->get("carrier");
        // si ça existe pas 
        if(!$carrier){
            // Je récupere depuis la bdd 
            $carrier = $this->carrierRepo->findAll()[0];
                // Un fois récupere j'extrait les informations
                $carrier = [
                    "id" => $carrier->getId(),
                    "name" => $carrier->getName(),
                    "description" => $carrier->getDescription(),
                    "price" => $carrier->getPrice()
                ];               
                // Je récupere via la session les transporteur 
                $this->update("carrier", $carrier);
        }

        $result["carrier"] = $carrier;
        $result['sub_total_with_carrier'] = $result['sub_total'] + $carrier['price'];

        return $result;
    }


}