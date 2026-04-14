<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\User;
use App\Form\ContactFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Contrôleur du formulaire de contact.
 *
 * Réservé aux utilisateurs connectés (ROLE_USER).
 * À la soumission :
 * - Sauvegarde en BDD (table contact)
 * - Email de notification à l'admin via Mailjet
 * - Email de confirmation à l'expéditeur via Mailjet
 *
 * @author Gheorghina Costincianu
 */
#[Route('/contact', name: 'app_contact_')]
#[IsGranted('ROLE_USER')]
final class ContactController extends AbstractController
{
    /**
     * @param EntityManagerInterface $em     Gestionnaire d'entités Doctrine
     * @param MailerInterface        $mailer Service d'envoi d'emails Symfony
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MailerInterface $mailer,
    ) {}

    /**
     * Affiche et traite le formulaire de contact.
     *
     * Pré-remplit automatiquement l'email depuis les données
     * de l'utilisateur connecté. À la soumission, sauvegarde
     * le message en BDD et envoie deux emails via Mailjet :
     * une notification à l'admin et une confirmation à l'expéditeur.
     *
     * @param Request $request Requête HTTP courante
     *
     * @return Response
     */
    #[Route('', name: 'index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        // dd($_ENV['MAILER_DSN']);
        /** @var User $user */
        $user = $this->getUser();

        $contact = new Contact();

        // Pré-remplissage de l'email avec celui de l'utilisateur connecté
        $contact->setEmail($user->getEmail());
        // dd('avant send');
        $form = $this->createForm(ContactFormType::class, $contact);
        $form->handleRequest($request);
        
        // dd($form->isSubmitted(), $form->isValid()); 

        if ($form->isSubmitted() && $form->isValid()) {
            //  dd($form->isValid());

            // Horodatage automatique à la soumission
            $contact->setCreatedAt(new \DateTimeImmutable());

            $this->em->persist($contact);
            $this->em->flush();

            // Premier email — notification admin
            try {
                $this->sendAdminNotification($contact);
            } catch (\Exception $e) {
                // On continue même si l'email admin échoue
            }

            sleep(1);

            // Seul l'email admin est envoyé en développement
            // L'email de confirmation sera activé en production avec Mailjet
            try {
                // Et je  remets sendConfirmationToUser() quand mon compte Mailjet sera débloqué — là j'aurais les deux emails sans aucune limitation.
                // Deuxième email — confirmation utilisateur
                $this->sendAdminNotification($contact);
                $this->sendConfirmationToUser($contact);
            } catch (\Exception $e) {
                // On continue même si l'email échoue
                // Temporaire — pour voir l'erreur exacte
                // $this->addFlash('danger', 'Erreur confirmation : ' . $e->getMessage());
            }
            // Dans tous les cas le message de succès s'affiche
            // car le message est bien sauvegardé en BDD
            $this->addFlash(
                'success',
                'Votre message a bien été envoyé ! Nous vous répondrons dans les plus brefs délais.'
            );

            return $this->redirectToRoute('app_contact_index');
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Envoie un email de notification à l'administrateur.
     *
     * @param Contact $contact Données du formulaire soumis
     *
     * @return void
     */
    private function sendAdminNotification(Contact $contact): void
    {
       
        $adminEmail = (new Email())
            ->from('gheorghina.costincianu@sfr.fr') //  MON EMAIL VALIDÉ
            ->to('gheorghina.costincianu@sfr.fr') 
            ->replyTo($contact->getEmail()) // IMPORTANT (pour répondre au client)

            ->subject('[C.G] Nouveau message : ' . $contact->getSubject())
            ->html($this->renderView('emails/contact_admin.html.twig', [
                'contact' => $contact,
            ]));
            
            // dd('avant send');
            $this->mailer->send($adminEmail);
           //  dd('email envoyé Symfony');
    }

    /**
     * Envoie un email de confirmation à l'expéditeur.
     *
     * @param Contact $contact Données du formulaire soumis
     *
     * @return void
     */
    private function sendConfirmationToUser(Contact $contact): void
    {
        $confirmEmail = (new Email())
            ->from('gheorghina.costincianu@sfr.fr')
            ->to($contact->getEmail())
            ->subject('C.G — Confirmation de votre message')
            ->html($this->renderView('emails/contact_confirmation.html.twig', [
                'contact' => $contact,
            ]));

        $this->mailer->send($confirmEmail);
    }
}