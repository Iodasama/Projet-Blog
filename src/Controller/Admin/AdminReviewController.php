<?php

declare(strict_types=1); // pour etre sur de l'affichage permet de reperer les erreurs par ex du string alors qu on attend un integer, comme c est permissif cela permet d etre sur que tout est bien typé, que la valeur de retour est bien celle qu on attend
namespace App\Controller\Admin;

use App\Entity\Review;
use App\Repository\BookRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;


class AdminReviewController extends AbstractController //commentaire test commit
{
    #[Route('/admin/insert-review', 'admin_insert_review')] // je cree ma route
    public function insertReview(Request $request, EntityManagerInterface $entityManager, BookRepository $bookRepository, ReviewRepository $reviewRepository): Response
    {
//          int $id,
//        $user = $userRepository->find($id);

        $books = $bookRepository->findAll();

        $reviews = $reviewRepository->findAll();


        if ($request->getMethod() === "POST") {

            $book = $bookRepository->find($request->request->get('book_id'));
            $title = $request->request->get('title');
            $content = $request->request->get('content'); // avec la methode Post la demande de création du user a été envoyée, je recupere les donnees POST

            $review = new Review(); // instancie une nouvelle classe User

            try {
                // je lui place les valeurs que je veux (title, content)
                $review->setTitle($title);
                $review->setContent($content);
//                $review->setUser($user);
                $review->setBook($book);
//                $review->setCreatedAt(new \DateTime('now'));


                //on instancie la classe entityManager pour ce faire on type EntityManagerInterface et on la placera en parametre ainsi que $EntityManagerInterface
                $entityManager->persist($review); // preparation de la requete
                //                dd($review);
                $entityManager->flush(); // execution de la requete

                //                dd($review);

                $this->addFlash('success', 'Review créé'); //je cree mon message flash

            } catch (\Exception $exception) {
                $this->addFlash('error', $exception->getMessage());
//                 il faut éviter de renvoyer le message directement récupéré depuis les erreurs SQL
//                $this->addFlash('error', 'error');

            }
        }

        return $this->render('Admin/page/user/insert-review.html.twig', ['books' => $books, 'reviews' => $reviews]); // je retourne le formulaire
    }

    #[Route('/admin/delete-review/{id}', name: 'delete_review')]
    public function deleteReview(int $id, ReviewRepository $reviewRepository, EntityManagerInterface $entityManager): Response
    {
        $review = $reviewRepository->find($id);
//         dd($review);

        if (!$review) {
            $html404 = $this->renderView('Admin/page/404.html.twig');
            return new Response($html404, 404);
        }

        try {
            $entityManager->remove($review); //preparation : preparer la requete Sql de suppression
            $entityManager->flush(); // execute : executer la requete préparée
            $this->addFlash('success', 'Review deleted sccessfully');
        } catch (\Exception $exception) {
            return $this->renderView('Admin/page/errormessage.html.twig', ['errorMessage' => $exception->getMessage()]);
        }
        return $this->redirectToRoute('admin_insert_review'); //bien mettre le name du path ici admin_articles_list_db non pas
    }


}