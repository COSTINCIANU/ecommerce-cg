<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Contact;
use App\Form\ContactResponseFormType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ContactCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {}

    public static function getEntityFqcn(): string
    {
        return Contact::class;
    }



    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Message de contact')
            ->setEntityLabelInPlural('Messages de contact')
            ->setPageTitle(Crud::PAGE_INDEX, 'Messages de contact reçus')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détail du message')
            ->setDefaultSort(['created_at' => 'DESC'])
            ->setSearchFields(['email', 'subject', 'content']);
    }



    public function configureActions(Actions $actions): Actions
    {
        // $replyAction = Action::new('test', 'TEST')
        //     ->linkToUrl(function (Contact $contact) {
        //         dd('BOUTON CLIQUÉ');
        //     });

        $replyAction = Action::new('reply', 'Répondre')
            ->linkToUrl(function (Contact $contact) {
                return '/admin/contact/repondre/' . $contact->getId();
            });

        return $actions
            ->disable(Action::NEW)
            ->disable(Action::EDIT)
            ->add(Crud::PAGE_INDEX, $replyAction)
            ->add(Crud::PAGE_DETAIL, $replyAction);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->onlyOnIndex();

        yield EmailField::new('email', 'Email expéditeur');

        yield TextField::new('subject', 'Sujet');

        yield TextareaField::new('content', 'Message')
            ->onlyOnIndex()
            ->setMaxLength(80);

        yield TextEditorField::new('content', 'Message')
            ->onlyOnDetail();

        yield DateTimeField::new('created_at', 'Reçu le')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->hideOnForm();

        yield DateTimeField::new('respondedAt', 'Répondu le')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->hideOnForm();

        yield TextareaField::new('response', 'Réponse envoyée')
            ->onlyOnDetail();
    }


    public function replyContact(AdminContext $context): Response
    {
        $contact = $context->getEntity()->getInstance();

        $form = $this->createForm(ContactResponseFormType::class);
        $form->handleRequest($context->getRequest());

        if ($form->isSubmitted() && $form->isValid()) {

            $responseText = $form->get('response')->getData();

            $email = (new Email())
                ->from('costincianu.gheorghina@gmail.com')
                ->to($contact->getEmail())
                ->subject('Réponse : ' . $contact->getSubject())
                ->html($this->renderView('emails/contact_response.html.twig', [
                    'contact' => $contact,
                    'response' => $responseText,
                ]));

            $this->mailer->send($email);

            $contact->setResponse($responseText);
            $contact->setRespondedAt(new \DateTimeImmutable());
            $this->em->flush();

            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/contact/reply.html.twig', [
            'contact' => $contact,
            'form' => $form->createView(),
        ]);
    }

}

