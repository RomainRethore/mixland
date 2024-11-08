<?php

namespace App\Controller;

use App\Entity\Mix;
use App\Repository\MixRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(MixRepository $mixRepository): Response
    {
        $mixes = $mixRepository->findAll();
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
            'mixes' => $mixes
        ]);
    }
}
