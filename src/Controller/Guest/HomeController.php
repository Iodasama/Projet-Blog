<?php

namespace App\Controller\Guest;


use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/home-page', name: 'home_page')]
    public function Home(categoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();

        return $this->render('Guest/page/home-page.html.twig',['categories' => $categories]);
//        dd (vars :'test');

    }
}
//    #[Route('/admin/categories-list-db', name: 'admin_categories_list_db')]
//    //Je cree la route, je lui passe le nom de admin_articles_list_db
//    public function adminListCategoriesFromDb(categoryRepository $categoryRepository): Response //Response pour le typage
//    {
//       ; //dans ma table Article je fais ma demande Select/ArticleRepo methode findAll, Doctrine bosse avec l'Entité->pour la requete SQl $categories = $articleRepository->findAll() Doctrine crée une instance de l'Entité (Article ici) par enregistrement (12 categories 12 enregistrements), je lui mets les valeurs que je veux (propriétés title, color?)je lui passe et Doctrine fait le reste du travail.
//        // La classe repository est un design pattern
//        //Les requetes Select sont mises dans Repository
//        //Je type la classe ArticleRepository et je crée une instance $articleRepository des lors je peux utliser ses methodes
//        //Je place en parametres ArticleRepository et $articleRepository
//        return $this->render('Guest/page/home-page.html.twig', ['categories' => $categories]);
//        //je retourne une réponse fichier twig code 200, une page qui contient mes categories
//        //la variable categories contient la variable $categories
//    }





