<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Product;
use App\Repository\CarrierRepository;
use App\Repository\ProductRepository;
use App\Repository\SettingRepository;
use App\Services\CartService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

/**
 * Tests unitaires du service CartService.
 *
 * Teste toutes les opérations du panier d'achat :
 * ajout, suppression, mise à jour des quantités,
 * vidage du panier et gestion du transporteur.
 *
 * Ces tests n'utilisent pas la base de données —
 * toutes les dépendances sont mockées (simulées).
 *
 * @author Gheorghina Costincianu
 */
class CartServiceTest extends TestCase
{
    private CartService $cartService;
    private Session $session;

    /** @var ProductRepository&MockObject */
    private MockObject $productRepo;

    /** @var CarrierRepository&MockObject */
    private MockObject $carrierRepo;

    /** @var SettingRepository&MockObject */
    private MockObject $settingRepo;

    /**
     * Initialisation avant chaque test.
     *
     * Crée une session en mémoire et mocke toutes les dépendances
     * du CartService pour isoler les tests de la base de données.
     */
    protected function setUp(): void
    {
        // Création d'une session en mémoire (pas de BDD, pas de requête HTTP)
        $this->session = new Session(new MockArraySessionStorage());

        // Création d'un RequestStack avec la session en mémoire
        $requestStack = new RequestStack();
        $request = new Request();
        $request->setSession($this->session);
        $requestStack->push($request);

        // Mock des repositories — on simule leur comportement
        $this->productRepo  = $this->createMock(ProductRepository::class);
        $this->carrierRepo  = $this->createMock(CarrierRepository::class);
        $this->settingRepo  = $this->createMock(SettingRepository::class);

        // Le settingRepo retourne null par défaut (pas de TVA configurée)
        $this->settingRepo
            ->method('findOneBy')
            ->willReturn(null);

        // Instanciation du CartService avec les mocks
        $this->cartService = new CartService(
            $requestStack,
            $this->productRepo,
            $this->carrierRepo,
            $this->settingRepo,
        );
    }

    // ═══════════════════════════════════════════════════════
    // TESTS — addToCart()
    // ═══════════════════════════════════════════════════════

    /**
     * Test : ajouter un nouveau produit au panier.
     *
     * Quand le panier est vide et qu'on ajoute le produit ID 1,
     * le panier doit contenir { 1: 1 }.
     */
    public function testAddToCartNewProduct(): void
    {
        $this->cartService->addToCart(1);

        $cart = $this->session->get('cart', []);

        $this->assertArrayHasKey(1, $cart);
        $this->assertSame(1, $cart[1]);
    }

    /**
     * Test : ajouter plusieurs fois le même produit.
     *
     * Ajouter le produit ID 1 deux fois doit donner
     * une quantité de 2 dans le panier.
     */
    public function testAddToCartIncreasesQuantity(): void
    {
        $this->cartService->addToCart(1);
        $this->cartService->addToCart(1);

        $cart = $this->session->get('cart', []);

        $this->assertSame(2, $cart[1]);
    }

    /**
     * Test : ajouter une quantité spécifique.
     *
     * Ajouter le produit ID 1 avec une quantité de 3
     * doit donner { 1: 3 } dans le panier.
     */
    public function testAddToCartWithSpecificCount(): void
    {
        $this->cartService->addToCart(1, 3);

        $cart = $this->session->get('cart', []);

        $this->assertSame(3, $cart[1]);
    }

    /**
     * Test : ajouter plusieurs produits différents.
     *
     * Le panier doit contenir tous les produits ajoutés.
     */
    public function testAddMultipleDifferentProducts(): void
    {
        $this->cartService->addToCart(1);
        $this->cartService->addToCart(2);
        $this->cartService->addToCart(3);

        $cart = $this->session->get('cart', []);

        $this->assertCount(3, $cart);
        $this->assertArrayHasKey(1, $cart);
        $this->assertArrayHasKey(2, $cart);
        $this->assertArrayHasKey(3, $cart);
    }

    // ═══════════════════════════════════════════════════════
    // TESTS — removeToCart()
    // ═══════════════════════════════════════════════════════

    /**
     * Test : retirer un produit du panier.
     *
     * Ajouter le produit ID 1 (quantité 2) puis en retirer 1
     * doit laisser { 1: 1 } dans le panier.
     */
    public function testRemoveToCartDecreasesQuantity(): void
    {
        $this->cartService->addToCart(1, 2);
        $this->cartService->removeToCart(1, 1);

        $cart = $this->session->get('cart', []);

        $this->assertSame(1, $cart[1]);
    }

    /**
     * Test : retirer complètement un produit du panier.
     *
     * Si la quantité retirée est égale ou supérieure à
     * la quantité en panier, le produit doit être supprimé.
     */
    public function testRemoveToCartRemovesProductWhenQuantityReachesZero(): void
    {
        $this->cartService->addToCart(1, 1);
        $this->cartService->removeToCart(1, 1);

        $cart = $this->session->get('cart', []);

        $this->assertArrayNotHasKey(1, $cart);
    }

    /**
     * Test : retirer un produit qui n'est pas dans le panier.
     *
     * Ne doit pas générer d'erreur — le panier reste inchangé.
     */
    public function testRemoveToCartProductNotInCart(): void
    {
        $this->cartService->addToCart(1, 2);
        $this->cartService->removeToCart(99); // produit inexistant

        $cart = $this->session->get('cart', []);

        // Le panier ne doit pas avoir changé
        $this->assertArrayHasKey(1, $cart);
        $this->assertSame(2, $cart[1]);
    }

    /**
     * Test : retirer plus que la quantité disponible.
     *
     * Si on retire 5 d'un produit qui en a 2,
     * le produit doit être complètement supprimé.
     */
    public function testRemoveToCartMoreThanAvailable(): void
    {
        $this->cartService->addToCart(1, 2);
        $this->cartService->removeToCart(1, 5);

        $cart = $this->session->get('cart', []);

        $this->assertArrayNotHasKey(1, $cart);
    }

    // ═══════════════════════════════════════════════════════
    // TESTS — clearCart()
    // ═══════════════════════════════════════════════════════

    /**
     * Test : vider complètement le panier.
     *
     * Après clearCart(), la session 'cart' doit être un tableau vide.
     */
    public function testClearCart(): void
    {
        $this->cartService->addToCart(1, 2);
        $this->cartService->addToCart(2, 3);
        $this->cartService->clearCart();

        $cart = $this->session->get('cart', []);

        $this->assertEmpty($cart);
    }

    /**
     * Test : vider un panier déjà vide.
     *
     * Ne doit pas générer d'erreur.
     */
    public function testClearEmptyCart(): void
    {
        $this->cartService->clearCart();

        $cart = $this->session->get('cart', []);

        $this->assertEmpty($cart);
    }

    // ═══════════════════════════════════════════════════════
    // TESTS — get() et update()
    // ═══════════════════════════════════════════════════════

    /**
     * Test : récupérer une clé inexistante retourne un tableau vide.
     *
     * get() doit retourner [] si la clé n'existe pas en session.
     */
    public function testGetReturnsEmptyArrayForUnknownKey(): void
    {
        $result = $this->cartService->get('unknown_key');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test : update() met bien à jour la session.
     *
     * Après update('cart', [1 => 5]), get('cart') doit retourner [1 => 5].
     */
    public function testUpdateStoresValueInSession(): void
    {
        $this->cartService->update('cart', [1 => 5]);
        $cart = $this->cartService->get('cart');

        $this->assertSame([1 => 5], $cart);
    }

    // ═══════════════════════════════════════════════════════
    // TESTS — updateCarrier()
    // ═══════════════════════════════════════════════════════

    /**
     * Test : mettre à jour le transporteur en session.
     *
     * updateCarrier() doit stocker les données du transporteur
     * sous la clé 'carrier' en session.
     */
    public function testUpdateCarrierStoresCarrierInSession(): void
    {
        $carrier = [
            'id'          => 1,
            'name'        => 'Chronopost',
            'description' => 'Livraison express',
            'price'       => 1099,
        ];

        $this->cartService->updateCarrier($carrier);

        $stored = $this->session->get('carrier', []);

        $this->assertSame('Chronopost', $stored['name']);
        $this->assertSame(1099, $stored['price']);
    }

    // ═══════════════════════════════════════════════════════
    // TESTS — getCartDetails()
    // ═══════════════════════════════════════════════════════

    /**
     * Test : getCartDetails() retourne une structure correcte pour un panier vide.
     *
     * Même avec un panier vide, getCartDetails() doit retourner
     * un tableau avec les clés attendues et un transporteur par défaut.
     */
    public function testGetCartDetailsWithEmptyCart(): void
    {
        // Mock du transporteur par défaut
        $mockCarrier = $this->createMock(\App\Entity\Carrier::class);
        $mockCarrier->method('getId')->willReturn(1);
        $mockCarrier->method('getName')->willReturn('Chronopost');
        $mockCarrier->method('getDescription')->willReturn('Livraison express');
        $mockCarrier->method('getPrice')->willReturn(1099);

        $this->carrierRepo
            ->method('findAll')
            ->willReturn([$mockCarrier]);

        $result = $this->cartService->getCartDetails();

        // Vérification de la structure retournée
        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('sub_total', $result);
        $this->assertArrayHasKey('cart_count', $result);
        $this->assertArrayHasKey('quantity', $result);
        $this->assertArrayHasKey('carrier', $result);
        $this->assertArrayHasKey('sub_total_with_carrier', $result);

        // Panier vide — totaux à zéro
        $this->assertEmpty($result['items']);
        $this->assertSame(0, $result['sub_total']);
        $this->assertSame(0, $result['cart_count']);
    }

    /**
     * Test : getCartDetails() calcule correctement les totaux.
     *
     * Avec un produit à 1000 centimes (10,00 €) et une quantité de 2,
     * le sous-total doit être 2000 centimes (20,00 €).
     */
    public function testGetCartDetailsCalculatesTotalsCorrectly(): void
    {
        // Création d'un produit mock
        $mockProduct = $this->createMock(Product::class);
        $mockProduct->method('getId')->willReturn(1);
        $mockProduct->method('getName')->willReturn('Robe de plage');
        $mockProduct->method('getSlug')->willReturn('robe-de-plage');
        $mockProduct->method('getDescription')->willReturn('Belle robe d\'été');
        $mockProduct->method('getSoldePrice')->willReturn(1000); // 10,00 €
        $mockProduct->method('getRegularPrice')->willReturn(1500);
        $mockProduct->method('getImageUrls')->willReturn([]);

        $this->productRepo
            ->method('find')
            ->with(1)
            ->willReturn($mockProduct);

        // Mock transporteur
        $mockCarrier = $this->createMock(\App\Entity\Carrier::class);
        $mockCarrier->method('getId')->willReturn(1);
        $mockCarrier->method('getName')->willReturn('Chronopost');
        $mockCarrier->method('getDescription')->willReturn('Express');
        $mockCarrier->method('getPrice')->willReturn(500); // 5,00 €

        $this->carrierRepo
            ->method('findAll')
            ->willReturn([$mockCarrier]);

        // Ajout du produit au panier (quantité 2)
        $this->cartService->addToCart(1, 2);

        $result = $this->cartService->getCartDetails();

        // Sous-total = 1000 * 2 = 2000 centimes
        $this->assertSame(2000, $result['sub_total']);
        // Quantité totale = 2
        $this->assertSame(2, $result['cart_count']);
        // Total avec transporteur = 2000 + 500 = 2500
        // $this->assertSame(2500, $result['sub_total_with_carrier']);

        // ✅ Après — on teste séparément
        $this->assertSame(2000, $result['sub_total']);              // sans port
        $this->assertSame(2500, $result['sub_total_with_carrier']); // avec port
        
        // 1 item dans le panier
        $this->assertCount(1, $result['items']);
    }

    /**
     * Test : getCartDetails() supprime les produits inexistants du panier.
     *
     * Si un produit en session n'existe plus en BDD,
     * il doit être automatiquement retiré du panier.
     */
    public function testGetCartDetailsRemovesNonExistentProducts(): void
    {
        // Le productRepo retourne null — produit supprimé de la BDD
        $this->productRepo
            ->method('find')
            ->willReturn(null);

        // Mock transporteur
        $mockCarrier = $this->createMock(\App\Entity\Carrier::class);
        $mockCarrier->method('getId')->willReturn(1);
        $mockCarrier->method('getName')->willReturn('Chronopost');
        $mockCarrier->method('getDescription')->willReturn('Express');
        $mockCarrier->method('getPrice')->willReturn(500);

        $this->carrierRepo
            ->method('findAll')
            ->willReturn([$mockCarrier]);

        // Ajout d'un produit qui n'existe pas en BDD
        $this->cartService->addToCart(999);

        $result = $this->cartService->getCartDetails();

        // Le produit inexistant doit être retiré
        $this->assertEmpty($result['items']);
        $this->assertSame(0, $result['sub_total']);

        // Le panier en session doit aussi être vide
        $cart = $this->session->get('cart', []);
        $this->assertEmpty($cart);
    }
}
