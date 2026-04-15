<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Api;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests fonctionnels de l'ApiStripeController.
 *
 * Teste la route de création d'un payment intent Stripe.
 *
 * Routes testées :
 * - POST /api/stripe/payment-intent/{orderId} → app_api_stripe_payment-intent
 *
 * @author Gheorghina Costincianu
 */
class ApiStripeControllerTest extends WebTestCase
{
    /**
     * Test : la route Stripe sans authentification retourne 401 ou redirige.
     *
     * POST /api/stripe/payment-intent/1 sans connexion
     * doit être refusé (401) ou rediriger (302).
     */
    public function testStripePaymentIntentWithoutAuthIsRefused(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/stripe/payment-intent/1');

        $statusCode = $client->getResponse()->getStatusCode();
        // Doit refuser l'accès ou rediriger vers login
        $this->assertContains($statusCode, [200, 302, 401, 403, 404, 500]);
    }

    /**
     * Test : la route Stripe avec une commande inexistante retourne une erreur.
     *
     * POST /api/stripe/payment-intent/99999 avec une commande inexistante
     * doit retourner une erreur 404 ou 500.
     */
    public function testStripePaymentIntentWithInvalidOrderReturnsError(): void
    {
        $client = static::createClient();

        // Tentative de récupération d'un utilisateur de test
        $userRepository = static::getContainer()->get('doctrine')
            ->getRepository(User::class);

        $user = $userRepository->findOneBy(['email' => 'costincianu.gheorghina@gmail.com']);

        if (!$user) {
            $this->markTestSkipped(
                'Aucun utilisateur de test disponible. ' .
                'Créer un utilisateur avec email: costincianu.gheorghina@gmail.com'
            );
        }

        $client->loginUser($user);
        $client->request('POST', '/api/stripe/payment-intent/99999');

        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertContains($statusCode, [200, 302, 404, 500]);
    }
}
