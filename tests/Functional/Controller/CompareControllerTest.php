<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests fonctionnels du CompareController.
 *
 * Teste les routes de comparaison de produits.
 *
 * Routes testées :
 * - ANY /compare                        → app_compare
 * - ANY /compare/add/{productId}        → app_add_to_compare
 * - ANY /compare/get                    → app_get_compare
 * - ANY /compare/remove/{productId}     → app_remove_to_compare
 *
 * @author Gheorghina Costincianu
 */
class CompareControllerTest extends WebTestCase
{
    /**
     * Test : la page compare est accessible.
     */
    public function testComparePageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/compare');

        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertContains($statusCode, [200, 302]);
    }

    /**
     * Test : ajouter à la comparaison répond correctement.
     */
    public function testAddToCompareResponds(): void
    {
        $client = static::createClient();
        $client->request('GET', '/compare/add/1');

        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertContains($statusCode, [200, 302, 404]);
    }

    /**
     * Test : récupérer la comparaison répond correctement.
     */
    public function testGetCompareResponds(): void
    {
        $client = static::createClient();
        $client->request('GET', '/compare/get');

        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertContains($statusCode, [200, 302]);
    }

    /**
     * Test : retirer de la comparaison répond correctement.
     */
    public function testRemoveFromCompareResponds(): void
    {
        $client = static::createClient();
        $client->request('GET', '/compare/remove/1');

        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertContains($statusCode, [200, 302, 404]);
    }
}
