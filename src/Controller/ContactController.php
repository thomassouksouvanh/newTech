<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Swift_Mailer;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ContactController extends AbstractController
{
    /**
     * @Route("/contact", name="contact")
     */
    public function index(Request $request,EntityManagerInterface $entityManagerInterface, Contact $contact, MailerInterface $mailer)
    {
        $form = $this->createForm(ContactType::class,$contact);
        $form->handleRequest($request);

         if($form->isSubmitted() && $form->isValid())
        {
            $contacts = $form->getData();
           //ici nous enverrons le mail
           $message = (new Email ())
           // on attribue l'expediteur
            ->from($contacts['email'])
            // on attribue le destinataire
            ->to($this->getParameter('my_adress'))
            // on creer le messsage avec la vue twig
            ->subject('Nouveau contact')
            ->html(
                $this->renderView(
                    'email/contact/notification',[
                        'contact'=> $contacts
                    ],
                    'text/html'
                )   
            );
            $mailer->send($message);
            $this->addFlash('message','votre message a bien été renvoyé');

            return $this->redirectToRoute('/');
        } 
        return $this->render('contact/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
