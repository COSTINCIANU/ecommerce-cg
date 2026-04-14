<?php


namespace App\Controller;

use App\Entity\User;
use App\Repository\AddressRepository;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Contrôleur de l'espace compte client.
 *
 * Affiche le tableau de bord personnel de l'utilisateur connecté :
 * informations personnelles, adresses de livraison et historique
 * des commandes avec téléchargement des factures PDF.
 *
 * @author Gheorghina Costincianu
 */
#[Route('/account', name: 'app_account')]
#[IsGranted('ROLE_USER')]
final class AccountController extends AbstractController
{
    /**
     * Affiche le tableau de bord du compte client.
     *
     * Récupère les adresses et les commandes de l'utilisateur
     * connecté pour les afficher dans l'espace personnel.
     *
     * @param AddressRepository $addressRepository Repository des adresses
     * @param OrderRepository   $orderRepository   Repository des commandes
     *
     * @return Response
     */
    #[Route('', name: '_index')]
    public function index(
        AddressRepository $addressRepository,
        OrderRepository $orderRepository
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        // Récupération des adresses de l'utilisateur
        $addresses = $addressRepository->findByUser($user);

        // Récupération des commandes de l'utilisateur
        // triées de la plus récente à la plus ancienne
        $orders = $orderRepository->findByUser($user);

        return $this->render('account/index.html.twig', [
            'addresses' => $addresses,
            'orders'    => $orders,
        ]);
    }
}


// namespace App\Controller;


// use App\Repository\AddressRepository;
// use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// use Symfony\Component\HttpFoundation\Response;
// use Symfony\Component\Routing\Attribute\Route;

// final class AccountController extends AbstractController
// {
//     #[Route('/account', name: 'app_account')]
//     public function index(AddressRepository $addressRepository): Response
//     {
//         // récupére l'utilisateur connecter
//         $user = $this->getUser();

//         $addresses = $addressRepository->findByUser($user);

//         return $this->render('account/index.html.twig', [
//             'controller_name' => 'AccountController',
//             'addresses' => $addresses,
//         ]);
//     }
// }
