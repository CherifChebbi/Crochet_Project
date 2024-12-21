<?php

namespace App\Controller;

use App\Entity\Media;
use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


class ProductController extends AbstractController
{
    //---------DASHBOARD-----------
    //dashboard
    #[Route('/dashboard', name: 'app_dashboard', methods: ['GET'])]
    public function back(ProductRepository $productRepository): Response
    {
        return $this->render('back/dashboard.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }
    //dashboard_product
    #[Route('/product', name: 'app_dashboard_product', methods: ['GET'])]
    public function product(ProductRepository $productRepository): Response
    {
        return $this->render('back/product.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }
    #[Route('/sign_up', name: 'app_dashboard_sign_up', methods: ['GET'])]
    public function sign_up(ProductRepository $productRepository): Response
    {
        return $this->render('back/sign-up.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }
    //---------FRONT-----------
    //front_index
    #[Route('/index', name: 'app_product_index', methods: ['GET'])]
    public function indexF(ProductRepository $productRepository): Response
    {
        return $this->render('front/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }
    //front_shop
    #[Route('/shop', name: 'app_product_shop', methods: ['GET'])]
    public function shop(ProductRepository $productRepository): Response
    {
        return $this->render('product/shop.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }
    //front_contact
    #[Route('/contact', name: 'app_product_contact', methods: ['GET'])]
    public function contact(ProductRepository $productRepository): Response
    {
        return $this->render('front/contact.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }


    //--------- AJOUT -----------
    #[Route('/product/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle multiple file uploads
            $files = $form->get('media')->getData(); // Get all the files

            if ($files) {
                foreach ($files as $file) {
                    $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $newFilename = $originalFilename.'-'.uniqid().'.'.$file->guessExtension();

                    // Move the file to the directory where media are stored
                    try {
                        $file->move(
                            $this->getParameter('media_directory'), // Directory path defined in services.yaml
                            $newFilename
                        );
                    } catch (IOExceptionInterface $e) {
                        echo "An error occurred while uploading the file: ".$e->getMessage();
                    }

                    // Create a new Media entity and associate it with the Product
                    $media = new Media();
                    $media->setUrl($newFilename);  // Store the file path in the URL field of Media entity
                    $media->setType($file->getClientMimeType());  // Store the mime type (e.g., image/jpeg, video/mp4)
                    $product->addMedia($media);  // Add the media to the product
                }
            }

            // Persist the product and media to the database
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('app_dashboard', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    //---------SHOW back-----------
    #[Route('/product/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    //---------EDIT back -----------
    #[Route('/product/{id}/edit', name: 'app_product_edit')]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        // Create the form for the product
        $form = $this->createForm(ProductType::class, $product);

        // Handle the request
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Process the uploaded media files
            $mediaFiles = $form->get('media')->getData();
            $newMediaCount = 0;

            if ($mediaFiles) {
                foreach ($mediaFiles as $file) {
                    // Generate a unique filename
                    $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                    $file->move($this->getParameter('media_directory'), $fileName);

                    // Create a new Media entity and associate it with the product
                    $media = new Media();
                    $media->setUrl($fileName);
                    $media->setProduct($product);

                    $entityManager->persist($media);
                    $newMediaCount++;
                }
            }

            // Update the number of media by adding the new ones
            $product->setNbrMedia($product->getNbrMedia() + $newMediaCount);

            // Persist and flush the changes
            $entityManager->persist($product);
            $entityManager->flush();

            // Redirect to the product detail page
            return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
        }

        return $this->render('product/edit.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }
    //---------DELETE back-----------
    //with show
    #[Route('/product/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
    //---------DELETE SIMPLE back----------------
    #[Route('/product/{id}/delete', name: 'app_product_delete_simple')]
    public function deletePays( Product $product, EntityManagerInterface $entityManager): Response
    {
        $media = $product->getMedia();

        foreach ($media as $media) {
            $entityManager->remove($media);
        }

        $entityManager->remove($product);
        $entityManager->flush();

        return $this->redirectToRoute('app_dashboard');
    }
    //-----------------------------------------------------------------------------------------------
    
}
