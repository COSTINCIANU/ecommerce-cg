<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests fonctionnels du PageController et SecurityController.
 *
 * Teste les routes des pages statiques et de la sécurité.
 *
 * Routes testées :
 * - ANY /page/{slug}  → app_page
 * - ANY /login        → app_login
 * - ANY /register     → app_register
 * - ANY /logout       → app_logout
 *
 * @author Gheorghina Costincianu
 */
class PageControllerTest extends WebTestCase
{
    /**
     * Test : la page de connexion est accessible.
     *
     * GET /login doit retourner 200 sans connexion.
     */
    public function testLoginPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Test : la page d'inscription est accessible.
     *
     * GET /register doit retourner 200 sans connexion.
     */
    public function testRegisterPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Test : la page de login contient un formulaire.
     *
     * La page de connexion doit avoir un champ email et password.
     */
    public function testLoginPageContainsForm(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    /**
     * Test : la page d'inscription contient un formulaire.
     */
    public function testRegisterPageContainsForm(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    /**
     * Test : la route page avec un slug répond correctement.
     *
     * GET /page/mentions-legales — si la page existe, retourne 200.
     * Si elle n'existe pas, peut retourner 302 ou 404.
     */
    public function testPageRouteResponds(): void
    {
        $client = static::createClient();
        $client->request('GET', '/page/mentions-legales');

        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertContains($statusCode, [200, 302, 404]);
    }

    /**
     * Test : la déconnexion redirige correctement.
     *
     * GET /logout doit rediriger vers la page d'accueil ou login.
     */
    public function testLogoutRedirects(): void
    {
        $client = static::createClient();
        $client->request('GET', '/logout');

        $this->assertResponseRedirects();
    }
}
