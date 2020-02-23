<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ResetPassType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class SecurityController extends AbstractController
{
    /**
    * @Route("/forgottenPassword", name="app_forgotten_password")
    */
    public function forgetPassword(EntityManagerInterface $entityManagerInterface,Request $request, UserRepository $userRepository,MailerInterface $mailer, TokenGeneratorInterface $tokenGeneratorInterface)
    {
        $form = $this->createForm(ResetPassType::class);
        $form->handleRequest($request);

        if($form->isValid() && $form->isSubmitted())
        {
            // On récupère les données
            $data = $form->getData();

            // On cherche un utilisateur ayant cet e-mail
            $user = $userRepository->findOneByEmail($data['email']);

            // Si l'utilisateur n'existe pas
            if($user === null)
            {
                $this->addFlash('danger','Cette adresse n\'existe pas');
                
                // On retourne sur la page de connexion
                return $this->redirectToRoute('app_login');

            }

            // On génère un token
            $token = $tokenGeneratorInterface->generateToken();
            
            // On essaie d'écrire le token en base de données
            try{
                $user->setReset($token);
                $entityManagerInterface->persist($user);
                $entityManagerInterface->flush();
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());
                return $this->redirectToRoute('app_login');
            }

            // On génère l'URL de réinitialisation de mot de passe
            $url = $this->generateUrl('app_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

            // On génère l'email
            $message = (new Email ())
            ->from($this->getParameter('my_adress'))
            ->to($user->getEmail())
            ->subject('Mot de passe oublié')
            ->html(
                "Bonjour,<br><br>Une demande de réinitialisation de mot de passe a été effectuée pour le site Nouvelle-Techno.fr. Veuillez cliquer sur le lien suivant : " . $url,
                'text/html'
            );

            // On envoie l'e-mail
            $mailer->send($message);

            // On crée le message flash de confirmation
            $this->addFlash('message', 'L\'email de réinitialisation du mot de passe envoyé !');

            // On redirige vers la page de login
            return $this->redirectToRoute('app_login');
        }

        // On envoie le formulaire à la vue
        return $this->render('security/forgotten_password.html.twig',
            ['form' => $form->createView()]);

    }

    /**
 * @Route("/reset_pass/{token}", name="app_reset_password")
 */
public function resetPassword(Request $request,$token, UserPasswordEncoderInterface $passwordEncoder)
{
    // On cherche un utilisateur avec le token donné
    $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['token' => $token]);

    // Si l'utilisateur n'existe pas
    if ($user === null) {
        // On affiche une erreur
        $this->addFlash('danger', 'Token Inconnu');
        return $this->redirectToRoute('app_login');
    }

    // Si le formulaire est envoyé en méthode post
    if ($request->isMethod('POST')) {
        // On supprime le token
        $user->setResetToken(null);

        // On chiffre le mot de passe
        $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('password')));

        // On stocke
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        // On crée le message flash
        $this->addFlash('message', 'Mot de passe mis à jour');
        // On redirige vers la page de connexion
        return $this->redirectToRoute('app_login');
    }else {
        // Si on n'a pas reçu les données, on affiche le formulaire
        return $this->render('security/reset_password.html.twig', ['token' => $token]);
    }

}

    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall');
    }
}
