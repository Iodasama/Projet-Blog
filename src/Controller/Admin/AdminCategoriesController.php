<?php

declare(strict_types=1); // pour etre sur de l'affichage permet de reperer les erreurs par ex du string alors qu on attend un integer, comme c est permissif cela permet d etre sur que tout est bien typé, que la valeur de retour est bien celle qu on attend
namespace App\Controller\Admin;


use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

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
    public function updateCategories(int $id, EntityManagerInterface $entityManager, Request $request, CategoryRepository $categoryRepository, SluggerInterface $slugger, ParameterBagInterface $params): Response
    {
        $category = $categoryRepository->find($id);

        $categoryCreateForm = $this->createForm(CategoryType::class, $category);
        $categoryCreateFormView = $categoryCreateForm->createView();

        $categoryCreateForm->handleRequest($request);

        if ($categoryCreateForm->isSubmitted() && $categoryCreateForm->isValid()) {
            $imageFile = $categoryCreateForm->get('image')->getData();
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
                $category->setImage($newFilename);

            }
            $category->setUpdatedAt(new \DateTime('NOW'));
            $entityManager->persist($category); // preparation de la requete
            $entityManager->flush(); // execution
            $this->addFlash('success', 'Category updated successfully');

        }


        return $this->render('Admin/page/update-categories.html.twig', ['categoryForm' => $categoryCreateFormView]);

    }

}
