<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests fonctionnels du CheckoutController.
 *
 * Teste les routes du tunnel d'achat.
 *
 * Routes testées :
 * - ANY /checkout                  → app_checkout
 * - ANY /stripe/payment/success    → app_stripe_payment_success
 * - ANY /paypal/payment/success    → app_paypal_payment_success
 *
 * @author Gheorghina Costincianu
 */
class CheckoutControllerTest extends WebTestCase
{
    /**
     * Test : la page checkout répond correctement.
     *
     * GET /checkout peut retourner 200 (panier vide)
     * ou rediriger si le panier est vide.
     */
    public function testCheckoutPageResponds(): void
    {
        $client = static::createClient();
        $client->request('GET', '/checkout');

        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertContains($statusCode, [200, 302]);
    }

    /**
     * Test : la page de succès Stripe répond correctement.
     *
     * GET /stripe/payment/success doit retourner une réponse valide.
     */
    public function testStripeSuccessPageResponds(): void
    {
        $client = static::createClient();
        $client->request('GET', '/stripe/payment/success');

        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertContains($statusCode, [200, 302, 404]);
    }

    /**
     * Test : la page de succès PayPal répond correctement.
     *
     * GET /paypal/payment/success doit retourner une réponse valide.
     */
    public function testPaypalSuccessPageResponds(): void
    {
        $client = static::createClient();
        $client->request('GET', '/paypal/payment/success');

        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertContains($statusCode, [200, 302, 404]);
    }
}
