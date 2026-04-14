<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests fonctionnels du AccountController.
 *
 * Teste les routes de l'espace compte client.
 * Ces routes nécessitent une authentification (ROLE_USER).
 *
 * Routes testées :
 * - ANY /account → app_account_index
 *
 * @author Gheorghina Costincianu
 */
class AccountControllerTest extends WebTestCase
{
    /**
     * Test : la page compte redirige vers login si non connecté.
     *
     * GET /account sans connexion doit rediriger
     * vers /login (code 302).
     */
    public function testAccountPageRedirectsWhenNotAuthenticated(): void
    {
        $client = static::createClient();
        $client->request('GET', '/account');

        // Doit rediriger vers la page de connexion
        $this->assertResponseRedirects();
        $this->assertResponseStatusCodeSame(302);
    }

    /**
     * Test : la redirection pointe vers la page de login.
     *
     * La redirection doit mener vers /login
     * quand l'utilisateur n'est pas connecté.
     */
    public function testAccountPageRedirectsToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/account');

        $this->assertResponseRedirects();
        $location = $client->getResponse()->headers->get('location');
        $this->assertStringContainsString('login', $location);
    }

    /**
     * Test : la page compte est accessible avec un utilisateur connecté.
     *
     * Simule la connexion d'un utilisateur et vérifie
     * que la page /account retourne 200.
     */
    public function testAccountPageIsAccessibleWhenAuthenticated(): void
    {
        $client = static::createClient();

        // Récupération d'un utilisateur depuis la BDD de test
        $userRepository = static::getContainer()->get('doctrine')
            ->getRepository(User::class);

        $user = $userRepository->findOneBy(['email' => 'costincianu.gheorghina@gmail.com']);

        // Si l'utilisateur n'existe pas en BDD de test, on skip
        if (!$user) {
            $this->markTestSkipped(
                'Aucun utilisateur de test disponible en BDD. ' .
                'Créer un utilisateur avec email: costincianu.gheorghina@gmail.com'
            );
        }

        // Connexion simulée
        $client->loginUser($user);
        $client->request('GET', '/account');

        $this->assertResponseIsSuccessful();
    }
}
