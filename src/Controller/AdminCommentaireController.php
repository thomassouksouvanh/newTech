<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdminCommentaireController extends AbstractController
{
    /**
     * @Route("/index/commentaire", name="index_commentaire")
     */
    public function index()
    {
        return $this->render('adminCommentaire/index.html.twig', [
            'controller_name' => 'AdminCommentaireController',
        ]);
    }
}
