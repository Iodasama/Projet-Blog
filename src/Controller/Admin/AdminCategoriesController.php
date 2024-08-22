<?php

declare(strict_types=1); // pour etre sur de l'affichage permet de reperer les erreurs par ex du string alors qu on attend un integer, comme c est permissif cela permet d etre sur que tout est bien typé, que la valeur de retour est bien celle qu on attend
namespace App\Controller\Admin;


use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminCategoriesController extends AbstractController
{

    #[Route('/admin/categories-list-db', name: 'admin_categories_list_db')]
    //Je cree la route, je lui passe le nom de admin_articles_list_db
    public function adminListCategoriesFromDb(categoryRepository $categoryRepository): Response //Response pour le typage
    {
        $categories = $categoryRepository->findAll(); //dans ma table Article je fais ma demande Select/ArticleRepo methode findAll, Doctrine bosse avec l'Entité->pour la requete SQl $categories = $articleRepository->findAll() Doctrine crée une instance de l'Entité (Article ici) par enregistrement (12 categories 12 enregistrements), je lui mets les valeurs que je veux (propriétés title, color?)je lui passe et Doctrine fait le reste du travail.
        // La classe repository est un design pattern
        //Les requetes Select sont mises dans Repository
        //Je type la classe ArticleRepository et je crée une instance $articleRepository des lors je peux utliser ses methodes
        //Je place en parametres ArticleRepository et $articleRepository
        return $this->render('Admin/page/categories-list.html.twig', ['categories' => $categories]);
        //je retourne une réponse fichier twig code 200, une page qui contient mes categories
        //la variable categories contient la variable $categories
    }

    #[Route('/admin/delete_category/{id}', name: 'delete_category')]
    public function deleteCategory(int $id, categoryRepository $categoryRepository, EntityManagerInterface $entityManager): Response
    {
        $category = $categoryRepository->find($id);

        if (!$category) {
            $html404 = $this->renderView('Admin/page/404.html.twig');
            return new Response($html404, 404);
        }

        try {
            $entityManager->remove($category); //preparation : preparer la requete Sql de suppression
            $entityManager->flush(); // execute : executer la requete préparée
            $this->addFlash('success', 'Category deleted successfully');
        } catch (\Exception $exception) {
            return $this->renderView('Admin/page/errormessage.html.twig', ['errorMessage' => $exception->getMessage()]);
        }
        return $this->redirectToRoute('admin_categories_list_db'); //bien mettre le name du path ici admin_articles_list_db non pas la route
    }

    #[Route('/admin/Admin-insert-category-formbuilder', name: 'Admin_category_insert_formbuilder')]
    public function insertCategories(EntityManagerInterface $entityManager, Request $request): Response
    {
        $category = new Category();
        $categoryCreateForm = $this->createForm(CategoryType::class, $category);
        $categoryCreateFormView = $categoryCreateForm->createView();

        $categoryCreateForm->handleRequest($request);
        if ($categoryCreateForm->isSubmitted() && $categoryCreateForm->isValid()) {

            $entityManager->persist($category);
            $entityManager->flush();
            $this->addFlash('success', 'Category inserted successfully');
        }


        return $this->render('Admin/page/insert-categories.html.twig', ['categoryForm' => $categoryCreateFormView]);

    }


    #[Route('/admin/Admin-update-category-formbuilder/{id}', name: 'Admin_category_update_formbuilder')]
    public function updateCategories(int $id, EntityManagerInterface $entityManager, Request $request, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->find($id);

        $categoryCreateForm = $this->createForm(CategoryType::class,$category);
        $categoryCreateFormView = $categoryCreateForm->createView();

        $categoryCreateForm->handleRequest($request);
        if ($categoryCreateForm->isSubmitted() && $categoryCreateForm->isValid()) {

            $entityManager->persist($category);
            $entityManager->flush(); //execution de la requete sql
            $this->addFlash('success', 'Category updated successfully');
        }


        return $this->render('Admin/page/update-categories.html.twig', ['categoryForm' => $categoryCreateFormView]);


    }
}