<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\UserAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, UserAuthenticator $authenticator,MailerInterface $mailer, TokenGeneratorInterface $tokenGeneratorInterface): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
                
            );
            // On génère un token et on l'enregistre
            $token = $tokenGeneratorInterface->generateToken();
            $user->setToken($token);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email
            // On crée le message
            $message = (new Email ())
            // On attribue l'expéditeur
            ->from($this->getParameter('mailer_from'))
            // On attribue le destinataire
            ->to($user->getEmail())
            // On crée le texte avec la vue
            ->subject('Nouveau compte')
            ->html(
                $this->renderView(
                    'contact/activationCompte.html.twig', ['token' => $user->getToken()]
                ),
                'text/html'
            );
            $mailer->send($message);
            
            $this->addFlash('succes','Un email vous a été envoyez, afin d\'activer votre compte');

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/activation/{token}", name="activation")
     */
    public function activationToken($token, UserRepository $user, EntityManagerInterface $entityManagerInterface)
    {
        // On recherche si un utilisateur avec ce token existe dans la base de données
        $userToken = $user->findOneBy(['token' => $token]);

        // Si aucun utilisateur n'est associé à ce token
        if(!$userToken)
        {
            // on renvoi une erreur 404
            throw $this->createNotFoundException('Cet utilisateur n\'existe pas');
        }

        // On supprime le token
        $userToken->setToken(null);
        $entityManagerInterface->persist($userToken);
        $entityManagerInterface->flush();

        $this->addFlash('succes','Votre compte est activé avec succès');

        return $this->redirectToRoute('homepage');
    }
}
