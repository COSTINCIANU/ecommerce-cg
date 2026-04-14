<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires de l'entité Order.
 *
 * Teste les getters/setters et la logique métier
 * de l'entité Order sans base de données.
 *
 * @author Gheorghina Costincianu
 */
class OrderTest extends TestCase
{
    private Order $order;

    /**
     * Initialisation avant chaque test.
     */
    protected function setUp(): void
    {
        $this->order = new Order();
    }

    /**
     * Test : le constructeur initialise created_at automatiquement.
     */
    public function testConstructorSetsCreatedAt(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->order->getCreatedAt());
    }

    /**
     * Test : isPaid est false par défaut.
     *
     * Une nouvelle commande ne doit pas être payée par défaut.
     */
    public function testIsPaidIsFalseByDefault(): void
    {
        $this->assertFalse($this->order->isPaid());
    }

    /**
     * Test : setIsPaid et isPaid fonctionnent correctement.
     */
    public function testSetIsPaid(): void
    {
        $this->order->setIsPaid(true);
        $this->assertTrue($this->order->isPaid());
    }

    /**
     * Test : setClientName et getClientName fonctionnent correctement.
     */
    public function testSetAndGetClientName(): void
    {
        $this->order->setClientName('Gheorghina Costincianu');
        $this->assertSame('Gheorghina Costincianu', $this->order->getClientName());
    }

    /**
     * Test : setStatus et getStatus fonctionnent correctement.
     */
    public function testSetAndGetStatus(): void
    {
        $this->order->setStatus('En cours de paiement');
        $this->assertSame('En cours de paiement', $this->order->getStatus());
    }

    /**
     * Test : setOrderCostTtc et getOrderCostTtc fonctionnent correctement.
     */
    public function testSetAndGetOrderCostTtc(): void
    {
        $this->order->setOrderCostTtc(5000);
        $this->assertSame(5000, $this->order->getOrderCostTtc());
    }

    /**
     * Test : setOrderCostHt et getOrderCostHt fonctionnent correctement.
     */
    public function testSetAndGetOrderCostHt(): void
    {
        $this->order->setOrderCostHt(4167);
        $this->assertSame(4167, $this->order->getOrderCostHt());
    }

    /**
     * Test : setCarrierName et getCarrierName fonctionnent correctement.
     */
    public function testSetAndGetCarrierName(): void
    {
        $this->order->setCarrierName('Chronopost');
        $this->assertSame('Chronopost', $this->order->getCarrierName());
    }

    /**
     * Test : setCarrierPrice et getCarrierPrice fonctionnent correctement.
     */
    public function testSetAndGetCarrierPrice(): void
    {
        $this->order->setCarrierPrice(1099);
        $this->assertSame(1099, $this->order->getCarrierPrice());
    }

    /**
     * Test : setUser et getUser fonctionnent correctement.
     */
    public function testSetAndGetUser(): void
    {
        $user = new User();
        $user->setEmail('client@gmail.com');

        $this->order->setUser($user);

        $this->assertSame($user, $this->order->getUser());
    }

    /**
     * Test : addOrderDetail ajoute un détail à la commande.
     */
    public function testAddOrderDetail(): void
    {
        $detail = new OrderDetails();
        $detail->setProductName('Robe de plage');
        $detail->setProductDescription('Belle robe');
        $detail->setProductSoldePrice(5800);
        $detail->setProductRegularPrice(8700);
        $detail->setQuantity(1);
        $detail->setSubTotal(5800);

        $this->order->addOrderDetail($detail);

        $this->assertCount(1, $this->order->getOrderDetails());
        $this->assertSame($this->order, $detail->getMyOrder());
    }

    /**
     * Test : removeOrderDetail retire un détail de la commande.
     */
    public function testRemoveOrderDetail(): void
    {
        $detail = new OrderDetails();
        $detail->setProductName('Pull');
        $detail->setProductDescription('Pull chaud');
        $detail->setProductSoldePrice(4500);
        $detail->setProductRegularPrice(6000);
        $detail->setQuantity(2);
        $detail->setSubTotal(9000);

        $this->order->addOrderDetail($detail);
        $this->assertCount(1, $this->order->getOrderDetails());

        $this->order->removeOrderDetail($detail);
        $this->assertCount(0, $this->order->getOrderDetails());
    }

    /**
     * Test : setStripeClientSecret et getStripeClientSecret fonctionnent.
     */
    public function testSetAndGetStripeClientSecret(): void
    {
        $this->order->setStripeClientSecret('pi_test_123456789');
        $this->assertSame('pi_test_123456789', $this->order->getStripeClientSecret());
    }

    /**
     * Test : une commande complète est correctement hydratée.
     *
     * Simule la création d'une commande comme dans le CheckoutController.
     */
    public function testFullOrderHydration(): void
    {
        $user = new User();
        $user->setEmail('client@gmail.com');
        $user->setFullName('Test Client');

        $this->order
            ->setClientName('Test Client')
            ->setBillingAddress('')
            ->setShippingAddress('')
            ->setQuantity(2)
            ->setOrderCostHt(4167)
            ->setOrderCostTtc(5000)
            ->setTaxe(20)
            ->setCarrierName('Chronopost')
            ->setCarrierPrice(1099)
            ->setCarrierId(1)
            ->setIsPaid(false)
            ->setStatus('En cours de paiement')
            ->setUser($user);

        $this->assertSame('Test Client', $this->order->getClientName());
        $this->assertSame(2, $this->order->getQuantity());
        $this->assertSame(5000, $this->order->getOrderCostTtc());
        $this->assertSame('Chronopost', $this->order->getCarrierName());
        $this->assertFalse($this->order->isPaid());
        $this->assertSame($user, $this->order->getUser());
    }
}
