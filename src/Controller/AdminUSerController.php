<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditUserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin", name="admin_")
 * Note: le admin_ pour concatener avec les autre name
 */
class AdminUSerController extends AbstractController
{
    /**
     * @Route("/index/user", name="index_user")
     */
    public function index()
    {
        return $this->render('adminUser/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * Liste des users
     * @Route("/listUser", name="list_users")
     *
     */
    public function userList(UserRepository $userRepository)
    {
        return $this->render('adminUser/list.html.twig',
        [
            'users' => $userRepository->findAll()
        ]);
        
    }

    /**
     * @Route(/"editUser/{id}", name="edit_user")
     */
    public function editUser(User $user, Request $request, EntityManagerInterface $entityManagerInterface)
    {
        $form = $this->createForm(EditUserType::class, $user);
        $form->handleRequest();

        if($form->isSubmitted() && $form->isValid())
        {      
            try {
                $entityManagerInterface->persist($user);
                $entityManagerInterface->flush();
            } catch (\Exception $e) {
                $this->addFlash('warning', 'Un problèmes s\'est passé, veuillez réessayer plus tard');
            }
            $this->addFlash('message','L\'utilisateur modifié avec succès' );
            return $this->redirectToRoute('list_users');
        }

        return $this->render('adminUser/editUser.html.twig',
        [
            'form' => $form->createView()
        ]);
    }
}