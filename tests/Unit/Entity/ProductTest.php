<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Product;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires de l'entité Product.
 *
 * Teste les getters/setters et la logique métier
 * de l'entité Product sans base de données.
 *
 * @author Gheorghina Costincianu
 */
class ProductTest extends TestCase
{
    private Product $product;

    /**
     * Initialisation avant chaque test.
     */
    protected function setUp(): void
    {
        $this->product = new Product();
    }

    /**
     * Test : le constructeur initialise created_at automatiquement.
     */
    public function testConstructorSetsCreatedAt(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->product->getCreatedAt());
    }

    /**
     * Test : setName génère automatiquement le slug.
     *
     * Quand on définit le nom "Robe de plage",
     * le slug doit être automatiquement "robe-de-plage".
     */
    public function testSetNameGeneratesSlugAutomatically(): void
    {
        $this->product->setName('Robe de plage');

        $this->assertSame('Robe de plage', $this->product->getName());
        $this->assertSame('robe-de-plage', $this->product->getSlug());
    }

    /**
     * Test : setName avec caractères spéciaux génère un slug propre.
     */
    public function testSetNameWithSpecialCharsGeneratesCleanSlug(): void
    {
        $this->product->setName('Culotte en dentelle élégante');

        $this->assertSame('culotte-en-dentelle-elegante', $this->product->getSlug());
    }

    /**
     * Test : setSoldePrice et getSoldePrice fonctionnent correctement.
     */
    public function testSetAndGetSoldePrice(): void
    {
        $this->product->setSoldePrice(5800);
        $this->assertSame(5800, $this->product->getSoldePrice());
    }

    /**
     * Test : setRegularPrice et getRegularPrice fonctionnent correctement.
     */
    public function testSetAndGetRegularPrice(): void
    {
        $this->product->setRegularPrice(8700);
        $this->assertSame(8700, $this->product->getRegularPrice());
    }

    /**
     * Test : le prix soldé est inférieur au prix régulier.
     *
     * Dans une boutique e-commerce, le prix soldé doit toujours
     * être inférieur au prix régulier.
     */
    public function testSoldePriceIsLessThanRegularPrice(): void
    {
        $this->product->setSoldePrice(5800);
        $this->product->setRegularPrice(8700);

        $this->assertLessThan(
            $this->product->getRegularPrice(),
            $this->product->getSoldePrice()
        );
    }

    /**
     * Test : setStock et getStock fonctionnent correctement.
     */
    public function testSetAndGetStock(): void
    {
        $this->product->setStock(25);
        $this->assertSame(25, $this->product->getStock());
    }

    /**
     * Test : setDescription et getDescription fonctionnent correctement.
     */
    public function testSetAndGetDescription(): void
    {
        $this->product->setDescription('Belle robe de plage pour l\'été');
        $this->assertSame('Belle robe de plage pour l\'été', $this->product->getDescription());
    }

    /**
     * Test : setImageUrls et getImageUrls fonctionnent correctement.
     */
    public function testSetAndGetImageUrls(): void
    {
        $urls = ['image1.webp', 'image2.webp', 'image3.webp'];
        $this->product->setImageUrls($urls);

        $this->assertSame($urls, $this->product->getImageUrls());
        $this->assertCount(3, $this->product->getImageUrls());
    }

    /**
     * Test : isBestSeller est null par défaut.
     */
    public function testIsBestSellerIsNullByDefault(): void
    {
        $this->assertNull($this->product->isBestSeller());
    }

    /**
     * Test : setIsBestSeller et isBestSeller fonctionnent correctement.
     */
    public function testSetAndGetIsBestSeller(): void
    {
        $this->product->setIsBestSeller(true);
        $this->assertTrue($this->product->isBestSeller());
    }

    /**
     * Test : setIsNewArrival et isNewArrival fonctionnent correctement.
     */
    public function testSetAndGetIsNewArrival(): void
    {
        $this->product->setIsNewArrival(true);
        $this->assertTrue($this->product->isNewArrival());
    }

    /**
     * Test : setIsFeatured et isFeatured fonctionnent correctement.
     */
    public function testSetAndGetIsFeatured(): void
    {
        $this->product->setIsFeatured(true);
        $this->assertTrue($this->product->isFeatured());
    }

    /**
     * Test : addCategory ajoute une catégorie au produit.
     */
    public function testAddCategory(): void
    {
        $category = new Category();
        $category->setName('Robes');

        $this->product->addCategory($category);

        $this->assertCount(1, $this->product->getCategories());
        $this->assertTrue($this->product->getCategories()->contains($category));
    }

    /**
     * Test : removeCategory retire une catégorie du produit.
     */
    public function testRemoveCategory(): void
    {
        $category = new Category();
        $category->setName('Jupes');

        $this->product->addCategory($category);
        $this->assertCount(1, $this->product->getCategories());

        $this->product->removeCategory($category);
        $this->assertCount(0, $this->product->getCategories());
    }

    /**
     * Test : addComment ajoute un avis au produit.
     */
    public function testAddComment(): void
    {
        $comment = new Comment();
        $comment->setContent('Super produit !');
        $comment->setRating(5);
        $comment->setEmail('client@test.com');

        $this->product->addComment($comment);

        $this->assertCount(1, $this->product->getComments());
        $this->assertSame($this->product, $comment->getProduct());
    }

    /**
     * Test : __toString retourne le nom du produit.
     */
    public function testToString(): void
    {
        $this->product->setName('T-shirt');
        $this->assertSame('T-shirt', (string) $this->product);
    }

    /**
     * Test : un produit complet est correctement hydraté.
     *
     * Simule la création d'un produit comme dans EasyAdmin.
     */
    public function testFullProductHydration(): void
    {
        $this->product
            ->setName('Robe de plage')
            ->setDescription('Robe légère pour l\'été')
            ->setSoldePrice(5800)
            ->setRegularPrice(8700)
            ->setStock(65)
            ->setImageUrls(['img1.webp', 'img2.webp'])
            ->setIsBestSeller(true)
            ->setIsNewArrival(true)
            ->setIsFeatured(true)
            ->setIsSpecialOffer(false);

        $this->assertSame('Robe de plage', $this->product->getName());
        $this->assertSame('robe-de-plage', $this->product->getSlug());
        $this->assertSame(5800, $this->product->getSoldePrice());
        $this->assertSame(8700, $this->product->getRegularPrice());
        $this->assertSame(65, $this->product->getStock());
        $this->assertTrue($this->product->isBestSeller());
        $this->assertFalse($this->product->isSpecialOffer());
    }
}
