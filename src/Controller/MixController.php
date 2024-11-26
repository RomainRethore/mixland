<?php

namespace App\Controller;

use App\Entity\Mix;
use App\Entity\User;
use App\Form\MixFormType;
use App\Repository\MixRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Id;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


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
    public function new(Request $request, EntityManagerInterface $entityManager, MailService $mailService, SluggerInterface $slugger, #[Autowire('%kernel.project_dir%/public/uploads/')] string $mixesDirectory): Response
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

                /** @var \App\Entity\User $user */
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

                /** @var \App\Entity\User $user */
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
    public function update(Request $request, int $id, Mix $mix, EntityManagerInterface $entityManager, SluggerInterface $slugger, #[Autowire('%kernel.project_dir%/public/uploads/')] string $mixesDirectory): Response
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

            if ($mixFile) {

                $originalFilename = pathinfo($mixFile->getClientOriginalName(), PATHINFO_FILENAME);

                $safeFilename = $slugger->slug($originalFilename);

                $newFilename = $safeFilename . '-' . uniqid() . '.' . $mixFile->getClientOriginalExtension();

                /** @var \App\Entity\User $user */
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

                /** @var \App\Entity\User $user */
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

        return $this->render('mix/update.html.twig', [
            'form' => $form
        ]);
    }
}
