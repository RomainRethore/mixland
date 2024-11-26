<?php

namespace App\Controller;

use App\Entity\Mix;
use App\Form\SearchFormType;
use App\Repository\MixRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MainController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(Request $request, MixRepository $mixRepository): Response
    {
        $form = $this->createForm(SearchFormType::class)->add('submit', SubmitType::class);

        $form->handleRequest($request);

        // dd($form);
        $mixes = $mixRepository->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            $searchTerm = $form->get('searchTerm')->getData();
            $mixes = $mixRepository->searchMixes($searchTerm);
            // dd($mixes);
        }
        dump($mixes);
        return $this->render('main/index.html.twig', [
            // 'controller_name' => 'MainController',
            'mixes' => $mixes,
            'form' => $form
        ]);
    }
}
