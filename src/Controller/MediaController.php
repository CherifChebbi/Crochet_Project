<?php

namespace App\Controller;

use App\Entity\Media;
use App\Form\MediaType;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/media')]
class MediaController extends AbstractController
{
    #[Route('/', name: 'app_media_index', methods: ['GET'])]
    public function index(MediaRepository $mediaRepository): Response
    {
        return $this->render('media/index.html.twig', [
            'media' => $mediaRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_media_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $medium = new Media();
        $form = $this->createForm(MediaType::class, $medium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($medium);
            $entityManager->flush();

            return $this->redirectToRoute('app_media_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('media/new.html.twig', [
            'medium' => $medium,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_media_show', methods: ['GET'])]
    public function show(Media $medium): Response
    {
        return $this->render('media/show.html.twig', [
            'medium' => $medium,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_media_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Media $medium, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MediaType::class, $medium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_media_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('media/edit.html.twig', [
            'medium' => $medium,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_media_delete', methods: ['POST'])]
    public function delete(Request $request, Media $medium, EntityManagerInterface $entityManager): Response
    {
        // Check CSRF token
        if ($this->isCsrfTokenValid('delete' . $medium->getId(), $request->request->get('_token'))) {
            
            // Get the associated product
            $product = $medium->getProduct(); // Assuming you have a 'getProduct()' method in the Media entity
            
            if ($product) {
                // Decrement the 'nbr_media' field on the associated product
                $product->setNbrMedia($product->getNbrMedia() - 1);
                
                // Save the product updates
                $entityManager->persist($product);
            }

            // Remove the media
            $entityManager->remove($medium);
            $entityManager->flush();
        }

        // Redirect back to the media index page (or to the product page)
        return $this->redirectToRoute('app_media_index', [], Response::HTTP_SEE_OTHER);
    }

}
