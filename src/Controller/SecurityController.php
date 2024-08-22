<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, EntityManagerInterface $entityManager): Response
    {
        $currentUser=$this->getUser();

       if( $this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_articles_list_db');
       } // si tu as le role ADMIN  alors redirection vers admin_articles_list_db
        //si tu as le role USER alors redirection vers 'path'

       if ($currentUser !==null && $this->isGranted('ROLE_USER')) {

//           dd($currentUser);
//           $user= $entityManager->getRepository(User::class)->findOneBy(['id'=>$currentUser->getId()]);
//           dd($user); soit cette methode soit celle du getId ci dessous
//           $id= $currentUser->getId();
           return $this->redirectToRoute('users_insert_review');

//           return $this->redirectToRoute('users_insert_review',['id'=>$id]); si on souhaite mettre un id mais faille dans la sécurité du coup n importe quel user une fois connecté

           //si tu as le role USER alors redirection vers 'path'
       }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);


    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
