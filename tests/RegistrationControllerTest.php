<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests fonctionnels du RegistrationController.
 *
 * @author Gheorghina Costincianu
 */
class RegistrationControllerTest extends WebTestCase
{
    /**
     * Test : la page d'inscription est accessible.
     */
    public function testRegisterPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
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
}