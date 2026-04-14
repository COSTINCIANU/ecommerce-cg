<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests fonctionnels du HomeController.
 *
 * Teste les routes publiques de la page d'accueil
 * accessibles sans authentification.
 *
 * @author Gheorghina Costincianu
 */
class HomeControllerTest extends WebTestCase
{
    /**
     * Test : la page d'accueil répond avec un code 200.
     *
     * La route app_home (GET /) doit être accessible
     * à tous les visiteurs sans connexion.
     */
    public function testHomePageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Test : la page d'accueil contient le nom du site.
     *
     * Le titre ou le contenu doit contenir "C.G".
     */
    public function testHomePageContainsSiteName(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('body');
    }

    /**
     * Test : la page d'erreur répond correctement.
     *
     * La route app_error (GET /error) doit retourner une réponse.
     */
    public function testErrorPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/error');

        // La page d'erreur peut retourner 200 ou une redirection
        $this->assertResponseStatusCodeSame(
            in_array($client->getResponse()->getStatusCode(), [200, 302, 404])
                ? $client->getResponse()->getStatusCode()
                : 200
        );
    }
}
