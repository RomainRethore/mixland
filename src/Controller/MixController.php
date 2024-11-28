<?php

namespace App\Controller;

use App\Entity\Mix;
use App\Entity\User;
use App\Form\MixFormType;
use App\Service\MailService;
use Doctrine\ORM\Mapping\Id;
use App\Service\FileUploader;
use App\Repository\MixRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Util\Json;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class MixController extends AbstractController
{
    #[Route('/mix', name: 'app_mix')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $mixes = $user->getMixes();

        return $this->render('mix/index.html.twig', [
            'controller_name' => 'MixController',
            'mixes' => $mixes
        ]);
    }

    #[Route('mix/new', name: 'app_mix_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, FileUploader $fileUploader, MailService $mailService, SluggerInterface $slugger, #[Autowire('%kernel.project_dir%/public/uploads/')] string $mixesDirectory): Response
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
                $newMixFile = $fileUploader->upload($mixFile);
                $mix->setAudio($newMixFile);
            }
            if ($mixCover) {
                $newMixCover = $fileUploader->upload($mixCover);
                $mix->setCover($newMixCover);
            }

            $entityManager->persist($mix);
            $entityManager->flush();

            $this->addFlash('success', 'Mix created successfully');

            $mailService->sendMixCreatedEmail($mix, $this->getUser());

            return $this->redirectToRoute('app_mix');
        }

        return $this->render('mix/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/mix/delete/{id}/{action}', name: 'app_mix_delete')]
    public function delete(int $id, string $action, Mix $mix, MixRepository $MixRepository, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $mix = $MixRepository->find($id);

        if ($action === 'confirm') {
            $entityManager->remove($mix);
            $entityManager->flush();

            $this->addFlash('message', 'Mix ' . $mix->getTitle() . ' deleted successfully');

            return $this->redirectToRoute('app_mix');
        }

        return $this->render('mix/delete.html.twig', [
            'mix' => $mix
        ]);
    }

    #[Route('/mix/{id}/update', name: 'app_mix_update')]
    public function update(Request $request, int $id, Mix $mix, FileUploader $fileUploader, EntityManagerInterface $entityManager, SluggerInterface $slugger, #[Autowire('%kernel.project_dir%/public/uploads/')] string $mixesDirectory): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $form = $this->createForm(MixFormType::class, $mix, [
            'attr' => ['id' => 'mix-form'],
        ])->add('submit', SubmitType::class, [
            'label' => 'Update Mix',
        ]);;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $mixFile = $form->get('audio')->getData();
            $mixCover = $form->get('cover')->getData();

            // if ($mixFile) {

            //     $originalFilename = pathinfo($mixFile->getClientOriginalName(), PATHINFO_FILENAME);

            //     $safeFilename = $slugger->slug($originalFilename);

            //     $newFilename = $safeFilename . '-' . uniqid() . '.' . $mixFile->getClientOriginalExtension();

            //     /** @var \App\Entity\User $user */
            //     $user = $this->getUser();

            //     $userDirectory = $mixesDirectory . '/' . $user->getId();

            //     if (!is_dir($userDirectory)) {
            //         mkdir($userDirectory, 0777, true);
            //     }

            //     try {
            //         $mixFile->move($userDirectory, $newFilename);
            //     } catch (FileException $e) {
            //         $this->addFlash('error', 'Mix file could not be uploaded');

            //         return $this->redirectToRoute('app_mix');
            //     }

            //     $mix->setAudio('uploads/' . $user->getId() . '/' . $newFilename);
            // }

            if ($mixFile) {
                $newMixFile = $fileUploader->upload($mixFile);
                $mix->setAudio($newMixFile);
            }

            if ($mixCover) {
                $newMixCover = $fileUploader->upload($mixCover);
                $mix->setCover($newMixCover);
            }

            $entityManager->persist($mix);
            $entityManager->flush();

            $this->addFlash('success', 'Mix created successfully');

            return $this->redirectToRoute('app_mix');
        }

        return $this->render('mix/update.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/mix/search', name: 'app_mix_search', methods: ['GET'])]
    public function searchMixes(Request $request, MixRepository $mixRepository): Response
    {
        // $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $searchTerm = $request->query->get('searchTerm');
        // dd($searchTerm);

        $mixes = $mixRepository->searchMixes($searchTerm);
        $response = new JsonResponse();
        $foundMixes = [];
        foreach ($mixes as $mix) {
            $foundMix = [
                'id' => $mix->getId(),
                'title' => $mix->getTitle(),
                'description' => $mix->getDescription(),
                'audio' => $mix->getAudio(),
                'cover' => $mix->getCover()
            ];
            $foundMixes[] = $foundMix;
        }
        $response->setData($foundMixes);

        return $response;
    }
}
