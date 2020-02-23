<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/account", name="account_")
 */
class AccountController extends AbstractController
{
    /**
     * @Route("/profil", name="profil")
     */
    public function profilUser()
    {
        $user = $this->getUser();

        return $this->render('account/profilUser.html.twig', [
            'user' => $user,
        ]);
    }
}
