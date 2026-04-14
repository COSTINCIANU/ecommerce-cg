<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait Timestampable
{
    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->created_at = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updated_at = new \DateTimeImmutable();
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    // ce que on doit avoir dans chaque entity si on veux que la date soit gere automatiquement 
    // namespace App\Entity;

    // use App\Entity\Traits\Timestampable;

    // #[ORM\HasLifecycleCallbacks]
    // #[ORM\Entity(repositoryClass: ProductRepository::class)]
    // class Product
    // {
    //     use Timestampable;
    // }
}