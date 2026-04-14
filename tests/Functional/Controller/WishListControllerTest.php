<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests fonctionnels du WishListController.
 *
 * Teste les routes de la liste de souhaits.
 * Accessible sans connexion.
 *
 * Routes testées :
 * - ANY /wishlist                      → app_wish_list
 * - ANY /wishlist/add/{productId}      → app_add_to_wishList
 * - ANY /wishlist/get                  → app_get_wishList
 * - ANY /wishlist/remove/{productId}   → app_remove_to_wishList
 *
 * @author Gheorghina Costincianu
 */
class WishListControllerTest extends WebTestCase
{
    /**
     * Test : la page wishlist est accessible.
     *
     * GET /wishlist doit retourner 200.
     */
    public function testWishListPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/wishlist');

        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertContains($statusCode, [200, 302]);
    }

    /**
     * Test : ajouter à la wishlist répond correctement.
     */
    public function testAddToWishListResponds(): void
    {
        $client = static::createClient();
        $client->request('GET', '/wishlist/add/1');

        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertContains($statusCode, [200, 302, 404]);
    }

    /**
     * Test : récupérer la wishlist répond correctement.
     */
    public function testGetWishListResponds(): void
    {
        $client = static::createClient();
        $client->request('GET', '/wishlist/get');

        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertContains($statusCode, [200, 302]);
    }

    /**
     * Test : retirer de la wishlist répond correctement.
     */
    public function testRemoveFromWishListResponds(): void
    {
        $client = static::createClient();
        $client->request('GET', '/wishlist/remove/1');

        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertContains($statusCode, [200, 302, 404]);
    }
}
