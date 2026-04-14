<?php

namespace App\EventSubscriber;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;


#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::postRemove)]
class DatabaseActivitySubscriber implements EventSubscriberInterface
{
    /** KernelInterface $appKernel  */
    private $appKernel;
    private $rootDir;

    public function __construct(KernelInterface $appKernel)
    {
        $this->appKernel = $appKernel;
        $this->rootDir = $appKernel->getProjectDir();
    }
   
    /**
     * Les venements de suppression et de mis a jour
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // on intercepte la de mis a jour
            Events::postUpdate,
            // on intercepte les evenement de supression
            Events::postRemove,
        ];
    }


    /**
     * Remove
     *
     * @param PostRemoveEventArgs $args
     * @return void
     */
    public function postRemove(PostRemoveEventArgs $args): void 
    {
        $this->logActivity('remove', $args->getObject());
    }


    /**
     * Update
     *
     * @param PostUpdateEventArgs $args
     * @return void
     */
    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $this->logActivity('update', $args->getObject());
    }



    /**
     * Action Log
     *
     * @param string $action
     * @param mixed $entity
     * @return void
     */
    public function logActivity(string $action, mixed $entity): void 
    {
        if(($entity instanceof Product) && $action === "remove"){
            // remove image
            $imageUrls = $entity->getImageUrls();
            foreach($imageUrls as $imageUrl) {
                $filelink = $this->rootDir . "/public/assets/images/products/" . $imageUrl;
                
                
                // Permet de suprimer les images lie a cette table Product
                $this->deleteImage($filelink);
            }
        }

        if (($entity instanceof Category) && $action === "remove"){
            // remove image Category
            // Permet de supprimer un fichier 
            $filename = $entity->getImageUrl();

            $filelink = $this->rootDir . "/public/assets/images/categories/" . $filename;

            // Permet de suprimer les images lie a cette table Category
            $this->deleteImage($filelink);
           

        }
        
    }


    /**
     * Remove
     *
     * @param string $filelink
     * @return void
     */
    public function deleteImage(string $filelink): void {
          try {
                $result = unlink($filelink);
            } catch (\Throwable $th){

            }
    }
}
