<?php

namespace App\Controller\Api;

use App\Entity\Address;
use App\Repository\AddressRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/api')]
final class ApiAddressController extends AbstractController 
{
    #[Route('/address', name: 'app_post_address', methods: ['POST'])]
    public function index(Request $req, 
        EntityManagerInterface $manager, 
        AddressRepository $addressRepository): Response
    {
        $user = $this->getUser();

        if (!$user){
            return $this->json([
                "isSuccess" => false,
                "message" => "Pas d'autorisation !",
                "data" => [],
            ]);
        }

        $formData = $req->getPayload();

        $address = new Address();
        $address->setName($formData->get('name'))
                ->setClientName($formData->get('client_name')?? '')
                ->setStreet($formData->get('street'))
                ->setCodePostal($formData->get('code_postal'))
                ->setCity($formData->get('city'))
                ->setState($formData->get('state'))
                ->setUser($user);

                // dd($address);

        $manager->persist($address);
        $manager->flush();

        $addresses = $addressRepository->findByUser($user);

        foreach ($addresses as $key => $address) {
            // je reprend l'user et je le retire
           $address->setUser(null);
            $addresses[$key] = $address;
        }

        return $this->json([
            "isSuccess" => true, 
            "data" => $addresses
        ]);
    }


    #[Route('/address/{id}', name: 'app_api_put_address', methods: ['PUT'])]
    public function update(int $id, 
        Request $req, 
        EntityManagerInterface $manager, 
        AddressRepository $addressRepository): Response
    {
        $user = $this->getUser();

        if (!$user){
            return $this->json([
                "isSuccess" => false,
                "message" => "Pas d'autorisation !",
                "data" => [],
            ]);
        }

        $address = $addressRepository->findOneById($id);

         if(!$address){
            return $this->json([
                "isSuccess" => false,
                "message" => "Adresse introuvable !",
                "data" => [],
            ]);
        }

        if($user !== $address->getUser()) {
            return $this->json([
                    "isSuccess" => false,
                    "message" => "Pas d'autorisation !",
                    "data" => [],
                ]);
        }

        // Start Update
        $formData = $req->getPayload();

         $address->setName($formData->get('name'))
                // ->setClientName($formData->get('client_name')?? '')
                ->setClientName($formData->get('client_name'))
                ->setStreet($formData->get('street'))
                ->setCity($formData->get('city'))
                ->setCodePostal($formData->get('code_postal'))
                ->setState($formData->get('state'));
        
        // on persiste en bdd l'addresse
        $manager->persist($address);
        $manager->flush();
        
        $addresses = $addressRepository->findByUser($user);
        foreach ($addresses as $key => $address) {
            // je reprend l'user et je le retire
           $address->setUser(null);
           $addresses[$key] = $address;
        }

        return $this->json([
            "isSuccess" => true, 
            "data" => $addresses
        ]);
    }



    #[Route('/address/{id}', name: 'app_api_delete_address', methods: ['DELETE'])]
    public function delete(int $id, 
        Request $req, 
        EntityManagerInterface $manager, 
        AddressRepository $addressRepository): Response
    {
        $user = $this->getUser();

        if (!$user){
            return $this->json([
                "isSuccess" => false,
                "message" => "Pas d'autorisation !",
                "data" => [],
            ]);
        }

        $address = $addressRepository->findOneById($id);

         if(!$address){
            return $this->json([
                "isSuccess" => false,
                "message" => "Adresse introuvable !",
                "data" => [],
            ]);
        }

        if ($user !== $address->getUser()) {
            return $this->json([
                    "isSuccess" => false,
                    "message" => "Pas d'autorisation !",
                    "data" => [],
                ]);
        }

        $manager->remove($address);
        $manager->flush();

        
        $addresses = $addressRepository->findByUser($user);
        foreach ($addresses as $key => $address) {
            // je reprend l'user et je le retire
           $address->setUser(null);

            $addresses[$key] = $address;
        }

        return $this->json([
            "isSuccess" => true, 
            "data" => $addresses
        ]);
    }


}
