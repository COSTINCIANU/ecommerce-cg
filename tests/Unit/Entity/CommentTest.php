<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Comment;
use App\Entity\Product;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires de l'entité Comment.
 *
 * Teste les getters/setters et la logique métier
 * de l'entité Comment sans base de données.
 *
 * @author Gheorghina Costincianu
 */
class CommentTest extends TestCase
{
    private Comment $comment;

    /**
     * Initialisation avant chaque test.
     */
    protected function setUp(): void
    {
        $this->comment = new Comment();
    }

    /**
     * Test : le constructeur initialise created_at automatiquement.
     *
     * Un nouveau Comment doit avoir une date de création
     * définie automatiquement dans le constructeur.
     */
    public function testConstructorSetsCreatedAt(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->comment->getCreatedAt());
    }

    /**
     * Test : isPublished est false par défaut.
     *
     * Un nouveau commentaire doit être en attente
     * de validation (non publié) par défaut.
     */
    public function testIsPublishedIsFalseByDefault(): void
    {
        $this->assertFalse($this->comment->isPublished());
    }

    /**
     * Test : setRating et getRating fonctionnent correctement.
     */
    public function testSetAndGetRating(): void
    {
        $this->comment->setRating(5);
        $this->assertSame(5, $this->comment->getRating());
    }

    /**
     * Test : setContent et getContent fonctionnent correctement.
     */
    public function testSetAndGetContent(): void
    {
        $this->comment->setContent('Super produit, je recommande !');
        $this->assertSame('Super produit, je recommande !', $this->comment->getContent());
    }

    /**
     * Test : setEmail et getEmail fonctionnent correctement.
     */
    public function testSetAndGetEmail(): void
    {
        $this->comment->setEmail('test@gmail.com');
        $this->assertSame('test@gmail.com', $this->comment->getEmail());
    }

    /**
     * Test : setIsPublished passe le commentaire à publié.
     */
    public function testSetIsPublishedToTrue(): void
    {
        $this->comment->setIsPublished(true);
        $this->assertTrue($this->comment->isPublished());
    }

    /**
     * Test : setAuthor et getAuthor fonctionnent correctement.
     */
    public function testSetAndGetAuthor(): void
    {
        $user = new User();
        $user->setEmail('auteur@gmail.com');

        $this->comment->setAuthor($user);

        $this->assertSame($user, $this->comment->getAuthor());
    }

    /**
     * Test : setProduct et getProduct fonctionnent correctement.
     */
    public function testSetAndGetProduct(): void
    {
        $product = new Product();
        $product->setName('Robe de plage');

        $this->comment->setProduct($product);

        $this->assertSame($product, $this->comment->getProduct());
    }

    /**
     * Test : setCreatedAt et getCreatedAt fonctionnent correctement.
     */
    public function testSetAndGetCreatedAt(): void
    {
        $date = new \DateTimeImmutable('2026-04-14 10:00:00');
        $this->comment->setCreatedAt($date);

        $this->assertSame($date, $this->comment->getCreatedAt());
    }

    /**
     * Test : un commentaire complet est correctement hydraté.
     *
     * Simule la création d'un avis complet comme dans le contrôleur.
     */
    public function testFullCommentHydration(): void
    {
        $user = new User();
        $user->setEmail('client@gmail.com');
        $user->setFullName('Test Client');

        $product = new Product();
        $product->setName('Culotte en dentelle');
        $product->setDescription('Belle culotte');
        $product->setSoldePrice(3500);
        $product->setRegularPrice(5000);
        $product->setImageUrls([]);

        $this->comment
            ->setAuthor($user)
            ->setProduct($product)
            ->setRating(4)
            ->setContent('Très beau produit, qualité au rendez-vous !')
            ->setEmail($user->getEmail())
            ->setIsPublished(false);

        $this->assertSame($user, $this->comment->getAuthor());
        $this->assertSame($product, $this->comment->getProduct());
        $this->assertSame(4, $this->comment->getRating());
        $this->assertSame('Très beau produit, qualité au rendez-vous !', $this->comment->getContent());
        $this->assertSame('client@gmail.com', $this->comment->getEmail());
        $this->assertFalse($this->comment->isPublished());
    }
}
