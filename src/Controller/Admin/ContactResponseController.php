<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Contact;
use App\Form\ContactResponseFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Contrôleur de réponse aux messages de contact.
 *
 * Accessible uniquement aux administrateurs (ROLE_ADMIN).
 * Route classique Symfony — appelée depuis le bouton
 * "Répondre" du dashboard EasyAdmin.
 *
 * @author Gheorghina Costincianu
 */
#[Route('/admin/contact', name: 'admin_contact_')]
#[IsGranted('ROLE_ADMIN')]
class ContactResponseController extends AbstractController
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly EntityManagerInterface $em,
    ) {}

    /**
     * Affiche le formulaire de réponse à un message de contact et traite son envoi.
     *
     * Accessible uniquement aux administrateurs (ROLE_ADMIN).
     * Cette méthode :
     * - Affiche le message original du client
     * - Présente un formulaire de rédaction de réponse
     * - Envoie la réponse par email au client via Mailjet
     * - Sauvegarde la réponse et la date d'envoi en base de données
     * - Redirige vers la liste des messages après l'envoi
     *
     * Si l'envoi email échoue, un message d'erreur est affiché
     * mais la redirection est effectuée dans tous les cas.
     *
     * @param Contact $contact Message de contact concerné (résolu via l'id dans l'URL)
     * @param Request $request Requête HTTP courante (GET = affichage, POST = traitement)
     *
     * @return Response Page du formulaire ou redirection vers la liste des messages
     */
    #[Route('/repondre/{id}', name: 'reply', methods: ['GET', 'POST'])]
    public function reply(Contact $contact, Request $request): Response
    {
        $form = $this->createForm(ContactResponseFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $responseText = $form->get('response')->getData();

            try {
                $email = (new Email())
                    ->from('costincianu.gheorghina@gmail.com')
                    ->to($contact->getEmail())
                    ->subject('C.G — Réponse : ' . $contact->getSubject())
                    ->html($this->renderView('emails/contact_response.html.twig', [
                        'contact'  => $contact,
                        'response' => $responseText,
                    ]));

                $this->mailer->send($email);

                $contact->setResponse($responseText);
                $contact->setRespondedAt(new \DateTimeImmutable());
                $this->em->flush();

                $this->addFlash('success', 'Réponse envoyée à ' . $contact->getEmail());

            } catch (\Exception $e) {
                $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
            }

            // Redirection dans tous les cas — succès ou échec email
            // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
            // $url = $adminUrlGenerator
            //     ->setController(ContactCrudController::class)
            //     ->setAction('index')
            //     ->generateUrl();

            // return $this->redirect($url);

            // return $this->redirectToRoute('admin_contact_index');

            // $this->redirectToRoute('admin', [
            //     'crudControllerFqcn' => ContactCrudController::class,
            // ]);


            $this->addFlash('success', 'Réponse envoyée à ' . $contact->getEmail());

            return $this->redirectToRoute('admin_contact_index');
        }

        return $this->render('admin/contact/reply.html.twig', [
            'contact' => $contact,
            'form'    => $form->createView(),
        ]);
    }
}