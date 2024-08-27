<?php

declare(strict_types=1); // pour etre sur de l'affichage permet de reperer les erreurs par ex du string alors qu on attend un integer, comme c est permissif cela permet d etre sur que tout est bien typé, que la valeur de retour est bien celle qu on attend
namespace App\Controller\Admin;


use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class AdminArticlesController extends AbstractController
{

    #[Route('/admin/articles-list-db', name: 'admin_articles_list_db')]
    //Je cree la route, je lui passe le nom de admin_articles_list_db
    public function adminListArticlesFromDb(ArticleRepository $articleRepository): Response //Response pour le typage
    {
        $articles = $articleRepository->findArticlesHome(); //dans ma table Article je fais ma demande Select/ArticleRepo methode findAll, Doctrine bosse avec l'Entité->pour la requete SQl $articles = $articleRepository->findAll() Doctrine crée une instance de l'Entité (Article ici) par enregistrement (12 articles 12 enregistrements), je lui mets les valeurs que je veux (propriétés title, color?)je lui passe et Doctrine fait le reste du travail.
        // La classe repository est un design pattern
        //Les requetes Select sont mises dans Repository
        //Je type la classe ArticleRepository et je crée une instance $articleRepository des lors je peux utliser ses methodes
        //Je place en parametres ArticleRepository et $articleRepository
        return $this->render('Admin/page/articles-list.html.twig', ['articles' => $articles]);
        //je retourne une réponse fichier twig code 200, une page qui contient mes articles
        //la variable articles contient la variable $articles
    }

    #[Route('/admin/delete/{id}', name: 'delete_article')]
    public function deleteArticle(int $id, articleRepository $articleRepository, EntityManagerInterface $entityManager): Response
    {
        $article = $articleRepository->find($id);

        if (!$article) {
            $html404 = $this->renderView('Admin/page/404.html.twig');
            return new Response($html404, 404);
        }

        try {
            $entityManager->remove($article); //preparation : preparer la requete Sql de suppression
            $entityManager->flush(); // execute : executer la requete préparée
            $this->addFlash('success', 'Article deleted successfully');
        } catch (\Exception $exception) {
            return $this->renderView('Admin/page/errormessage.html.twig', ['errorMessage' => $exception->getMessage()]);
        }
        return $this->redirectToRoute('admin_articles_list_db'); //bien mettre le name du path ici admin_articles_list_db non pas
    }

    #[Route('/admin/insert-formbuilder', name: 'admin_article_insert_formbuilder')]
    public function insertArticles(EntityManagerInterface $entityManager, Request $request, SluggerInterface $slugger, ParameterBagInterface $params): Response
    {
        $article = new Article();
        $articleCreateForm = $this->createForm(ArticleType::class, $article);
        $articleCreateFormView = $articleCreateForm->createView();

        $articleCreateForm->handleRequest($request);
        if ($articleCreateForm->isSubmitted() && $articleCreateForm->isValid()) {
            // on  récupère le fichier depuis le formulaire // au prealable dans le dossier Form Fichier ArticleType.phpje demande à symfony de ne pas gérer automatiquement le champs image, je prends la main.
            $imageFile = $articleCreateForm->get('image')->getData();


            // si il y a bien un fichier envoyé
            if ($imageFile) {
                // je récupère le nom du fichier (ici mes images ont des noms de fichiers avec des lettres, tirets du 6,  et chiffres et extensions en .jpg)
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);

                // le slug je nettoie le nom en sortant tous les caractères spéciaux etc
                // je type la classe SluggerInterface  et je crée une instance $slugger des lors je peux utliser ses methodes
                //Je place en parametres SluggerInterface et $slugger
                $safeFilename = $slugger->slug($originalFilename);

                // je rajoute un identifiant unique au nom (que l'on pourra verifier en bdd une fois inseré apres le flush)
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    // je récupère le chemin de la racine du projet
                    $rootPath = $params->get('kernel.project_dir');
                    // je déplace le fichier dans le dossier /public/images en partant de la racine
                    // du projet, et je renomme le fichier avec le nouveau nom (slugifié et identifiant unique)
                    $imageFile->move( $rootPath.'/public/images', $newFilename);
                } catch (FileException $e){
                    dd($e->getMessage());

                    //  Le code où le programmeur pense qu'une exception peut se produire est placé dans le trybloc. Cela ne signifie pas qu'une exception se produira ici. Cela signifie que cela pourrait se produire ici, et que le programmeur est conscient de cette possibilité. Le type d'erreur que l on attend est placé dans le catchbloc. Celui-ci contient également tout le code qui doit être exécuté si une exception se produit.
                    //si l exception se produit on aura un message du style : Some error message
                }

                // je stocke dans la propriété image de l'entité article le nom du fichier
                $article->setImage($newFilename);

            }

            $entityManager->persist($article); // preparation de la requete
            $entityManager->flush(); // execution
            $this->addFlash('success', 'Article inserted successfully');
        }


        return $this->render('Admin/page/insert-articles.html.twig', ['articleForm' => $articleCreateFormView]);

    }

    #[Route('/admin/update-formbuilder/{id}', name: 'admin_article_update_formbuilder')]
    public function updateArticles(int $id, EntityManagerInterface $entityManager, Request $request, ArticleRepository $articleRepository,SluggerInterface $slugger, ParameterBagInterface $params): Response
    {
        $article = $articleRepository->find($id);

        $articleCreateForm = $this->createForm(ArticleType::class, $article);
        $articleCreateFormView = $articleCreateForm->createView();

        $articleCreateForm->handleRequest($request);
        if ($articleCreateForm->isSubmitted() && $articleCreateForm->isValid()) {
            $imageFile = $articleCreateForm->get('image')->getData();
            if ($imageFile) {
                // je récupère le nom du fichier (ici mes images ont des noms de fichiers avec des lettres, tirets du 6,  et chiffres et extensions en .jpg)
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);

                // le slug je nettoie le nom en sortant tous les caractères spéciaux etc
                // je type la classe SluggerInterface  et je crée une instance $slugger des lors je peux utliser ses methodes
                //Je place en parametres SluggerInterface et $slugger
                $safeFilename = $slugger->slug($originalFilename);

                // je rajoute un identifiant unique au nom (que l'on pourra verifier en bdd une fois inseré apres le flush)
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    // je récupère le chemin de la racine du projet
                    $rootPath = $params->get('kernel.project_dir');
                    // je déplace le fichier dans le dossier /public/images en partant de la racine
                    // du projet, et je renomme le fichier avec le nouveau nom (slugifié et identifiant unique)
                    $imageFile->move($rootPath . '/public/images', $newFilename);
                } catch (FileException $e) {
                    dd($e->getMessage());

                    //  Le code où le programmeur pense qu'une exception peut se produire est placé dans le trybloc. Cela ne signifie pas qu'une exception se produira ici. Cela signifie que cela pourrait se produire ici, et que le programmeur est conscient de cette possibilité. Le type d'erreur que l on attend est placé dans le catchbloc. Celui-ci contient également tout le code qui doit être exécuté si une exception se produit.
                    //si l exception se produit on aura un message du style : Some error message
                }

                // je stocke dans la propriété image de l'entité article le nom du fichier
                $article->setImage($newFilename);
            }


            $article->setUpdatedAt(new \DateTime('NOW'));
            $entityManager->persist($article);
            $entityManager->flush(); //execution de la requete sql
            $this->addFlash('success', 'Article updated successfully');
        }



        return $this->render('admin/page/update-articles.html.twig', ['articleForm' => $articleCreateFormView]);


    }


//    #[Route('/admin/update-formbuilder/{id}', name: 'admin_article_update_formbuilder')]
//    public function updateArticles(int $id, EntityManagerInterface $entityManager, Request $request, ArticleRepository $articleRepository, SluggerInterface $slugger, ParameterBagInterface $params): Response
//    {
//        $article = $articleRepository->find($id);
//
//        $articleCreateForm = $this->createForm(ArticleType::class, $article);
//        $articleCreateFormView = $articleCreateForm->createView();
//
//        $articleCreateForm->handleRequest($request);
//
//        if ($articleCreateForm->isSubmitted() && $articleCreateForm->isValid()) {
//
//            $imagefiles = $articleCreateForm->get('image')->getData();
//
//            if ($imagefiles)
//            {
//                $originalFilename = pathinfo($imagefiles->getClientOriginalName(), PATHINFO_FILENAME);
//                $safeFilename = $slugger->slug($originalFilename);
//                $newFilename = $safeFilename.'-'.uniqid().'.'.$imagefiles->guessExtension();
//                try
//                {
//                    $rootPath = $params->get('kernel.project_dir');
//                    $imagefiles->move( $rootPath.'/public/images', $newFilename);
//                } catch (FileException $exception)
//                {
//                    dd($exception->getMessage());
//                }
//                $article->setImage($newFilename);
//            }
//            $article->setUpdatedAt(new \DateTime('NOW')); // pour avoir la date de l'update lors d'un updatej
//            $entityManager->persist($article);
//            $entityManager->flush(); //execution de la requete sql
//            $this->addFlash('success', 'Article updated successfully');
//        }
//
//
//        return $this->render('Admin/page/update-articles.html.twig', ['articleForm' => $articleCreateFormView]);
//
//
//    }
}














//    #[Route('/admin/show-articles/{id}', name: 'admin_article_db_by_id')]
//    public function adminShowArticleById(int $id, ArticleRepository $articleRepository): Response
//    {
//        $article = $articleRepository->find($id);
//
//        if (!$article || !$article->isPublished()) {
//            $html404 = $this->renderView('Admin/page/404.html.twig');
//            return new Response($html404, 404);
//        }
//
//        return $this->render('Admin/page/show-articleById.html.twig', ['article' => $article]);
//    }
//    #[Route('/categories-list-db', name: 'categories_list_db')]
//
//    public function listCategoriesFromDb(CategoryRepository $categoryRepository): Response
//    {
//        $categories = $categoryRepository->findAll();
//
//        return $this->render('Guest/page/categories-list.html.twig', ['categories' => $categories]);
//    }
//
//    #[Route('/show-categories/{id}', name: 'category_db_by_id')]
//    public function showCategoryById(int $id, CategoryRepository $categoryRepository): Response
//    {
//        $category = $categoryRepository->find($id);
//
//        return $this->render('Guest/page/show-categoriesById.html.twig', ['category' => $category]);
//    }



