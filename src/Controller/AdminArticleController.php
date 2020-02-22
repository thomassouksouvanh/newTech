<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin", name="admin_")
 * Note: le admin_ pour concatener avec les autre name
 */
class AdminArticleController extends AbstractController
{
    /**
     * @Route("/index/article", name="index_article")
     */
    public function index()
    {
        return $this->render('adminArticle/index.html.twig', [
            'controller_name' => 'AdminArticleController',
        ]);
    }
}
