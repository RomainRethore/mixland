<?php

namespace App\Controller;

use App\Entity\Mix;
use App\Form\MixFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class MixController extends AbstractController
{
    #[Route('/mix', name: 'app_mix')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('mix/index.html.twig', [
            'controller_name' => 'MixController',
        ]);
    }

    #[Route('mix/new', name: 'app_mix_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, #[Autowire('%kernel.project_dir%/public/uploads/mixes/')] string $mixesDirectory): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $mix = new Mix();

        $form = $this->createForm(MixFormType::class, $mix, [
            'attr' => ['id' => 'mix-form']
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $mixFile = $form->get('audio')->getData();

            if ($mixFile) {

                $originalFilename = pathinfo($mixFile->getClientOriginalName(), PATHINFO_FILENAME);

                $safeFilename = $slugger->slug($originalFilename);

                $newFilename = $safeFilename . '-' . uniqid() . '.' . $mixFile->getClientOriginalExtension();

                $user = $this->getUser();

                $userDirectory = $mixesDirectory . '/' . $user->getId();

                if (!is_dir($userDirectory)) {
                    mkdir($userDirectory, 0777, true);
                }

                try {
                    $mixFile->move($userDirectory, $newFilename);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                $mix->setAudio($newFilename);
            }

            $entityManager->persist($mix);
            $entityManager->flush();

            $this->addFlash('success', 'Mix created successfully');

            return $this->redirectToRoute('app_mix');
        }

        return $this->render('mix/new.html.twig', [
            'form' => $form
        ]);
    }
}
