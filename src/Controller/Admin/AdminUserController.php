<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminUserController extends AbstractController
{



    #[Route('/admin/users/insert', 'admin_insert_user')] // je cree ma route
    public function insertAdminUser(UserPasswordHasherInterface $passwordHasher, Request $request, EntityManagerInterface $entityManager)
    {

        if ($request->getMethod() === "POST") {
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $pseudo = $request->request->get('pseudo'); // avec la methode Post la demande de création du user a été envoyée, je recupere les donnees POST
            $user = new User(); // on instancie une nouvelle classe User

            try {
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $password
                ); // j'instancie la classe $passwordHasher

                // je lui place les valeurs que je veux (email, password, role)
                $user->setEmail($email);
                $user->setPassword($hashedPassword);
                $user->setRoles(['ROLE_ADMIN']);
                $user->setPseudo($pseudo);
//                dd($user);
                //on instancie la classe entityManager pour ce faire on type EntityManagerInterface et on la placera en parametre ainsi que $EntityManagerInterface
                $entityManager->persist($user); // preparation de la requete
//                dd($user);
                $entityManager->flush(); // execution de la requete


                $this->addFlash('success', 'User créé'); //je cree mon message flash
                // Redirection vers la page qui affiche la liste des utilisateurs
                return $this->redirectToRoute('show_users');
            } catch (\Exception $exception) {
                // $this->addFlash('error', $exception->getMessage()); il faut éviter de renvoyer le message directement récupéré depuis les erreurs SQL
//dd($exception->getMessage());
                $this->addFlash('error', 'error');
            }
        }

        return $this->render('Admin/page/user/insert_user.html.twig'); // je retourne le formulaire
    }
    #[Route('/admin/show-users', name: 'show_users')]
    public function adminShowUsers(userRepository $userRepository): Response //Response pour le typage
    {
        $users = $userRepository->findAll();
        return $this->render('Admin/page/user/show-users.html.twig', ['users' => $users]);

    }

    #[Route('/admin/delete-user/{id}', name: 'delete_user')]
    public function deleteUser(int $id, userRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        //$users = $userRepository->findAll();
        $user = $userRepository->find($id);

        if (!$user) {
            $html404 = $this->renderView('Admin/page/404.html.twig');
            return new Response($html404, 404);
        }

        try {
            $entityManager->remove($user); //preparation : preparer la requete Sql de suppression
            $entityManager->flush(); // execute : executer la requete préparée
            $this->addFlash('success', 'User deleted successfully');
        } catch (\Exception $exception) {
            return $this->renderView('Admin/page/errormessage.html.twig', ['errorMessage' => $exception->getMessage()]);
        }
        return $this->redirectToRoute('show_users');
    }
}


//
//    #[Route('/admin/users/insert', 'admin_insert_user')] // je cree ma route
//    public function insertUser(UserPasswordHasherInterface $passwordHasher, Request $request, EntityManagerInterface $entityManager)
//    {
//
//        if ($request->getMethod() === "POST") {
//            $email = $request->request->get('email');
//            $password = $request->request->get('password'); // avec la methode Post la demande de création du user a été envoyée, je recupere les donnees POST
//
//            $user = new User(); // instancie une nouvelle classe User
//
//            try {
//                $hashedPassword = $passwordHasher->hashPassword(
//                    $user,
//                    $password
//                ); // j'instancie la classe $passwordHasher
//
//                // je lui place les valeurs que je veux (email, password, role)
//                $user->setEmail($email);
//                $user->setPassword($hashedPassword);
//                $user->setRoles(['ROLE_USER']);
//
//                //on instancie la classe entityManager pour ce faire on type EntityManagerInterface et on la placera en parametre ainsi que $EntityManagerInterface
//                $entityManager->persist($user); // preparation de la requete
//                $entityManager->flush(); // execution de la requete
//
//                $this->addFlash('success', 'User créé'); //je cree mon message flash
//
//            } catch (\Exception $exception) {
//                // $this->addFlash('error', $exception->getMessage()); il faut éviter de renvoyer le message directement récupéré depuis les erreurs SQL
//                $this->addFlash('error', 'error');
//            }
//        }
//
//        return $this->render('admin/page/user/insert_user.html.twig'); // je retourne le formulaire
//    }





