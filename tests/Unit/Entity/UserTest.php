<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Address;
use App\Entity\Comment;
use App\Entity\Order;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires de l'entité User.
 *
 * Teste les getters/setters et la logique métier
 * de l'entité User sans base de données.
 *
 * @author Gheorghina Costincianu
 */
class UserTest extends TestCase
{
    private User $user;

    /**
     * Initialisation avant chaque test.
     */
    protected function setUp(): void
    {
        $this->user = new User();
    }

    /**
     * Test : le constructeur initialise les collections.
     */
    public function testConstructorInitializesCollections(): void
    {
        $this->assertCount(0, $this->user->getAddresses());
        $this->assertCount(0, $this->user->getComments());
        $this->assertCount(0, $this->user->getOrders());
    }

    /**
     * Test : le constructeur initialise created_at automatiquement.
     */
    public function testConstructorSetsCreatedAt(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->user->getCreatedAt());
    }

    /**
     * Test : isVerified est false par défaut.
     */
    public function testIsVerifiedIsFalseByDefault(): void
    {
        $this->assertFalse($this->user->isVerified());
    }

    /**
     * Test : getRoles retourne toujours au moins ROLE_USER.
     *
     * Même si aucun rôle n'est défini, l'utilisateur doit
     * toujours avoir ROLE_USER (comportement natif Symfony).
     */
    public function testGetRolesAlwaysContainsRoleUser(): void
    {
        $roles = $this->user->getRoles();
        $this->assertContains('ROLE_USER', $roles);
    }

    /**
     * Test : setRoles et getRoles fonctionnent correctement.
     */
    public function testSetRolesAdminIncludesRoleUser(): void
    {
        $this->user->setRoles(['ROLE_ADMIN']);
        $roles = $this->user->getRoles();

        $this->assertContains('ROLE_ADMIN', $roles);
        $this->assertContains('ROLE_USER', $roles);
    }

    /**
     * Test : getRoles ne contient pas de doublons.
     *
     * Même si ROLE_USER est ajouté plusieurs fois,
     * il ne doit apparaître qu'une seule fois.
     */
    public function testGetRolesNoDuplicates(): void
    {
        $this->user->setRoles(['ROLE_USER', 'ROLE_USER']);
        $roles = $this->user->getRoles();

        $this->assertSame(array_unique($roles), $roles);
    }

    /**
     * Test : getUserIdentifier retourne l'email.
     */
    public function testGetUserIdentifierReturnsEmail(): void
    {
        $this->user->setEmail('gheorghina@gmail.com');
        $this->assertSame('gheorghina@gmail.com', $this->user->getUserIdentifier());
    }

    /**
     * Test : setEmail et getEmail fonctionnent correctement.
     */
    public function testSetAndGetEmail(): void
    {
        $this->user->setEmail('test@gmail.com');
        $this->assertSame('test@gmail.com', $this->user->getEmail());
    }

    /**
     * Test : setFullName et getFullName fonctionnent correctement.
     */
    public function testSetAndGetFullName(): void
    {
        $this->user->setFullName('Gheorghina Costincianu');
        $this->assertSame('Gheorghina Costincianu', $this->user->getFullName());
    }

    /**
     * Test : setCivility et getCivility fonctionnent correctement.
     */
    public function testSetAndGetCivility(): void
    {
        $this->user->setCivility('Mlle');
        $this->assertSame('Mlle', $this->user->getCivility());
    }

    /**
     * Test : addAddress ajoute une adresse à l'utilisateur.
     */
    public function testAddAddress(): void
    {
        $address = new Address();
        $address->setName('Maison');
        $address->setStreet('15 Impasse du Couchant');
        $address->setCity('Mèze');

        $this->user->addAddress($address);

        $this->assertCount(1, $this->user->getAddresses());
        $this->assertSame($this->user, $address->getUser());
    }

    /**
     * Test : removeAddress retire une adresse de l'utilisateur.
     */
    public function testRemoveAddress(): void
    {
        $address = new Address();
        $address->setName('Travail');
        $address->setStreet('32 Rue de la Rose');
        $address->setCity('Mèze');

        $this->user->addAddress($address);
        $this->assertCount(1, $this->user->getAddresses());

        $this->user->removeAddress($address);
        $this->assertCount(0, $this->user->getAddresses());
    }

    /**
     * Test : addOrder ajoute une commande à l'utilisateur.
     */
    public function testAddOrder(): void
    {
        $order = new Order();
        $order->setClientName('Gheorghina Costincianu');
        $order->setQuantity(1);
        $order->setOrderCostHt(4167);
        $order->setOrderCostTtc(5000);
        $order->setCarrierName('Chronopost');
        $order->setCarrierPrice(1099);
        $order->setCarrierId(1);
        $order->setStatus('En cours');

        $this->user->addOrder($order);

        $this->assertCount(1, $this->user->getOrders());
        $this->assertSame($this->user, $order->getUser());
    }

    /**
     * Test : addComment ajoute un commentaire à l'utilisateur.
     */
    public function testAddComment(): void
    {
        $comment = new Comment();
        $comment->setContent('Excellent produit !');
        $comment->setRating(5);
        $comment->setEmail('test@gmail.com');

        $this->user->addComment($comment);

        $this->assertCount(1, $this->user->getComments());
        $this->assertSame($this->user, $comment->getAuthor());
    }

    /**
     * Test : __toString retourne le full_name.
     */
    public function testToStringReturnsFullName(): void
    {
        $this->user->setFullName('Gheorghina Costincianu');
        $this->assertSame('Gheorghina Costincianu', (string) $this->user);
    }

    /**
     * Test : un utilisateur complet est correctement hydraté.
     */
    public function testFullUserHydration(): void
    {
        $this->user
            ->setEmail('costincianu.gheorghina@gmail.com')
            ->setFullName('Gheorghina Costincianu')
            ->setCivility('Mlle')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword('hashed_password')
            ->setIsVerified(true);

        $this->assertSame('costincianu.gheorghina@gmail.com', $this->user->getEmail());
        $this->assertSame('Gheorghina Costincianu', $this->user->getFullName());
        $this->assertSame('Mlle', $this->user->getCivility());
        $this->assertContains('ROLE_ADMIN', $this->user->getRoles());
        $this->assertTrue($this->user->isVerified());
    }
}
