<?php



namespace App\Controller;


use App\Entity\User;
use App\Repository\OrderRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Contrôleur de génération de factures PDF.
 *
 * Génère une facture PDF à la demande pour une commande payée.
 * Vérifie que la commande appartient bien à l'utilisateur connecté.
 * Utilise DomPDF pour convertir le template HTML en PDF.
 *
 * @author Gheorghina Costincianu
 */
#[Route('/facture', name: 'app_invoice_')]
#[IsGranted('ROLE_USER')]
class InvoiceController extends AbstractController
{
    /**
     * Génère et retourne la facture PDF d'une commande.
     *
     * Vérifie successivement :
     * 1. Que la commande existe (404 sinon)
     * 2. Que la commande appartient à l'utilisateur connecté (403 sinon)
     * 3. Que la commande est bien payée (403 sinon)
     *
     * @param int             $id              Identifiant de la commande
     * @param OrderRepository $orderRepository Repository des commandes
     *
     * @return Response Fichier PDF en téléchargement direct
     */
    #[Route('/{id}', name: 'download', methods: ['GET'])]
    public function download(int $id, OrderRepository $orderRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // 1. Récupération de la commande
        // $order = $orderRepository->find($id);

        $order = $orderRepository->findOneByIdWithDetails($id);

        if (!$order) {
            throw $this->createNotFoundException('Commande introuvable.');
        }

        // 2. Vérification que la commande appartient à l'utilisateur
        if ($order->getUser() !== $user) {
            throw $this->createAccessDeniedException(
                'Vous n\'avez pas accès à cette facture.'
            );
        }

        // 3. Vérification que la commande est payée
        if (!$order->isPaid()) {
            throw $this->createAccessDeniedException(
                'La facture n\'est disponible que pour les commandes payées.'
            );
        }

        // 4. Rendu du template HTML de la facture
        $html = $this->renderView('pdf/invoice.html.twig', [
            'order' => $order,
            'user'  => $user,
        ]);

        // 5. Configuration de DomPDF
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);

        // 6. Génération du PDF
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // 7. Nom du fichier de téléchargement
        $filename = 'facture-commande-' . $order->getId() . '.pdf';

        // 8. Retour de la réponse PDF
        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }
}