<?php

namespace App\Controller;

use App\Entity\Mix;
use App\Entity\User;
use App\Form\MixFormType;
use App\Repository\MixRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Id;
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
    public function index(MixRepository $mixRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $mixes = $this->getUser()->getMixes();


        return $this->render('mix/index.html.twig', [
            'controller_name' => 'MixController',
            'mixes' => $mixes
        ]);
    }

    #[Route('mix/new', name: 'app_mix_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, #[Autowire('%kernel.project_dir%/public/uploads/')] string $mixesDirectory): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $mix = new Mix();
        $mix->addUser($this->getUser());

        $form = $this->createForm(MixFormType::class, $mix, [
            'attr' => ['id' => 'mix-form']
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $mixFile = $form->get('audio')->getData();
            $mixCover = $form->get('cover')->getData();

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
                    $this->addFlash('error', 'Mix file could not be uploaded');

                    return $this->redirectToRoute('app_mix');
                }

                $mix->setAudio('uploads/' . $user->getId() . '/' . $newFilename);
            }

            if ($mixCover) {

                $originalFilename = pathinfo($mixCover->getClientOriginalName(), PATHINFO_FILENAME);

                $safeFilename = $slugger->slug($originalFilename);

                $newFilename = $safeFilename . '-' . uniqid() . '.' . $mixCover->getClientOriginalExtension();

                $user = $this->getUser();

                $userDirectory = $mixesDirectory . '/' . $user->getId();

                if (!is_dir($userDirectory)) {
                    mkdir($userDirectory, 0777, true);
                }

                try {
                    $mixCover->move($userDirectory, $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Mix cover could not be uploaded');

                    return $this->redirectToRoute('app_mix');
                }

                $mix->setCover('uploads/' . $user->getId() . '/' . $newFilename);
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
