<?php

namespace App\Controller\Guest;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function Home()
    {

        return $this->render('Guest/page/index.html.twig');
//        dd (vars :'test');

    }
}



