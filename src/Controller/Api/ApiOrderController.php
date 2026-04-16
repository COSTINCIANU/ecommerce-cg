<?php

namespace App\Controller\Api;

use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ApiOrderController extends AbstractController
{
    #[Route('/api/order', name: 'app_api_api_order', methods: ['POST'])]
    public function update(
        Request $req, OrderRepository $ordersRepo, 
        EntityManagerInterface $em): Response
    {
        $data = $req->getPayload();

        $id = $data->get("orderId");
        $order = $ordersRepo->findOneById($id);

        if (!$order) {
            // return $this->redirectToRoute("/checkout");
            return $this->json(['error' => 'Commande introuvable'], 404); // ← JSON pas redirect
        }
        
        $order->setBillingAddress($data->get("billing_address"))
              ->setShippingAddress($data->get("shipping_address"));
            //   ->setComment($data->get("comment"))
        
        $em->persist($order);
        $em->flush();


        // return $this->render('api/api_order/index.html.twig', [
        //     'controller_name' => 'ApiOrderController',
        // ]);
         return $this->json(['success' => true, 'orderId' => $order->getId()]); // ← JSON ✅
    }
}
