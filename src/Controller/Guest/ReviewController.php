<?php

declare(strict_types=1); // pour etre sur de l'affichage permet de reperer les erreurs par ex du string alors qu on attend un integer, comme c est permissif cela permet d etre sur que tout est bien typé, que la valeur de retour est bien celle qu on attend
namespace App\Controller\Guest;

use App\Entity\Review;
use App\Repository\BookRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;


class ReviewController extends AbstractController //commentaire test commit
{
    #[Route('/users/insert-review/{id}', 'users_insert_review')] // je cree ma route
    public function insertReview(int $id, Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository, BookRepository $bookRepository,ReviewRepository $reviewRepository): Response
    {
        $user = $userRepository->find($id);
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
                $review->setUser($user);
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

        return $this->render('Guest/page/user/insert-review.html.twig', ['user' => $user, 'books' => $books,'reviews' => $reviews]); // je retourne le formulaire
    }

//    #[Route('/users/insert-review/{id}', name: 'users_insert_review')]
//    //Je cree la route, je lui passe le nom de admin_articles_list_db
//    public function showReviews(ReviewRepository $reviewRepository): Response //Response pour le typage
//    {
//        $reviews = $reviewRepository->findAll(); //dans ma table Article je fais ma demande Select/ArticleRepo methode findAll, Doctrine bosse avec l'Entité->pour la requete SQl $articles = $articleRepository->findAll() Doctrine crée une instance de l'Entité (Article ici) par enregistrement (12 articles 12 enregistrements), je lui mets les valeurs que je veux (propriétés title, color?)je lui passe et Doctrine fait le reste du travail.
//        // La classe repository est un design pattern
//        //Les requetes Select sont mises dans Repository
//        //Je type la classe ArticleRepository et je crée une instance $articleRepository des lors je peux utliser ses methodes
//        //Je place en parametres ArticleRepository et $articleRepository
//        return $this->render('Guest/page/user/insert-review.html.twig', ['reviews' => $reviews]);
//        //je retourne une réponse fichier twig code 200, une page qui contient mes articles
//        //la variable articles contient la variable $articles
//    }
//

}