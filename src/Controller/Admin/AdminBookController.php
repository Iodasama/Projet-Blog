<?php
namespace App\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class AdminBookController extends AbstractController {
#[Route('/admin/books/insert', 'admin_insert_book')] // je cree ma route
public function insertAdminBook( Request $request, EntityManagerInterface $entityManager)
{

if ($request->getMethod() === "POST") {
$title= $request->request->get('title');
$author = $request->request->get('author'); // avec la methode Post la demande de création du user a été envoyée, je recupere les donnees POST

$book = new \App\Entity\Book(); // on instancie une nouvelle classe book

try {


// je lui place les valeurs que je veux (email, password, role)
$book->setTitle($title);
$book->setAuthor($author);


//on instancie la classe entityManager pour ce faire on type EntityManagerInterface et on la placera en parametre ainsi que $EntityManagerInterface
$entityManager->persist($book); // preparation de la requete
//                dd($user);
$entityManager->flush(); // execution de la requete
    $this->addFlash('success', 'book créé'); //je cree mon message flash

} catch (\Exception $exception) {
     $this->addFlash('error', $exception->getMessage());
//     il faut éviter de renvoyer le message directement récupéré depuis les erreurs SQL
//    $this->addFlash('error', 'error');
}
}

    return $this->render('insert-review.html.twig'); // je retourne le formulaire
}

}
