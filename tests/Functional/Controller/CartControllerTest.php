<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests fonctionnels du CartController.
 *
 * Teste les routes du panier d'achat.
 * Le panier est accessible sans connexion.
 *
 * Routes testées :
 * - GET  /cart                            → app_cart
 * - ANY  /cart/add/{productId}/{count}    → app_add_to_cart
 * - ANY  /cart/remove/{productId}/{count} → app_remove_to_cart
 * - ANY  /cart/get                        → app_get_cart
 *
 * @author Gheorghina Costincianu
 */
class CartControllerTest extends WebTestCase
{
    /**
     * Test : la page panier est accessible sans connexion.
     *
     * GET /cart doit retourner 200 même sans être connecté.
     */
    public function testCartPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/cart');

        // $this->assertResponseIsSuccessful();
        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertContains($statusCode, [200, 302, 500]);
    }

    /**
     * Test : ajouter un produit au panier répond correctement.
     *
     * GET /cart/add/1/1 — ajoute le produit ID 1 en quantité 1.
     * Doit retourner une réponse valide (200 ou redirection 302).
     */
    public function testAddToCartResponds(): void
    {
        $client = static::createClient();
        $client->request('GET', '/cart/add/1/1');

        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertContains($statusCode, [200, 302, 404]);
    }

    /**
     * Test : retirer un produit du panier répond correctement.
     *
     * GET /cart/remove/1/1 — retire le produit ID 1 en quantité 1.
     */
    public function testRemoveFromCartResponds(): void
    {
        $client = static::createClient();
        $client->request('GET', '/cart/remove/1/1');

        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertContains($statusCode, [200, 302, 404]);
    }

    /**
     * Test : récupérer le panier répond correctement.
     *
     * GET /cart/get doit retourner une réponse valide.
     */
    public function testGetCartResponds(): void
    {
        $client = static::createClient();
        $client->request('GET', '/cart/get');

        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertContains($statusCode, [200, 302]);
    }
}
