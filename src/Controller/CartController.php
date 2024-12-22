<?php

// src/Controller/CartController.php

namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\ProductRepository;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    #[Route('/cart/add/{id}', name: 'cart_add', methods: ['POST'])]
    public function addToCart($id, Request $request, SessionInterface $session, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($id); // Charger avec les médias

        if (!$product) {
            return $this->json(['message' => 'Produit introuvable'], 404);
        }

        if (!$product->isAvailability()) {
            return $this->json(['message' => 'Produit non disponible'], 400);
        }
        $cart = $session->get('cart', []);

        // Ajouter ou mettre à jour le produit dans le panier
        if (!isset($cart[$id])) {
            $cart[$id] = [
                'product' => [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'price' => $product->getPrice(),
                    'media' => $product->getMedia()->toArray(), // Ajouter les médias
                ],
                'quantity' => 1,
            ];
        } else {
            $cart[$id]['quantity'] += 1; // Si le produit existe déjà, augmenter la quantité
        }

        // Sauvegarder dans la session
        $session->set('cart', $cart);

        // Retourner la réponse JSON avec le nombre d'articles dans le panier
        return $this->json([
            'message' => 'Produit ajouté au panier',
            'cartCount' => count($cart),
        ]);
    }
    

    #[Route('/cart/update/{id}', name: 'cart_update', methods: ['POST'])]
    public function updateQuantity($id, Request $request, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        // Vérifier si le produit est dans le panier
        if (isset($cart[$id])) {
            $quantity = $request->request->get('quantity');
            // Mettre à jour la quantité (assurez-vous que la quantité est un nombre valide)
            if (is_numeric($quantity) && $quantity > 0) {
                $cart[$id]['quantity'] = (int) $quantity;
            }
        }

        // Sauvegarder à nouveau le panier dans la session
        $session->set('cart', $cart);

        // Calculer le total
        $total = $this->calculateTotal($cart);

        // Retourner la réponse avec la mise à jour du panier
        return $this->redirectToRoute('cart_view');
    }

    private function calculateTotal($cart)
    {     
        $total = 0;
        foreach ($cart as $item) {
            // Utiliser les informations stockées dans le tableau du panier
            $total += $item['product']['price'] * $item['quantity'];
        }
        return $total;
    }

    #[Route('/cart', name: 'cart_view', methods: ['GET'])]
    public function viewCart(SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        $total = $this->calculateTotal($cart);
    
        return $this->render('cart/view.html.twig', [
            'cart' => $cart,
            'total' => $total
        ]);
    }
    
    #[Route('/cart/checkout', name: 'cart_checkout', methods: ['GET', 'POST'])]
    public function checkout(SessionInterface $session, Request $request): Response
    {
        $cart = $session->get('cart', []);

        if (!$cart) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('home');
        }

        $form = $this->createFormBuilder()
            ->add('name')
            ->add('surname')
            ->add('address')
            ->add('phone')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Sauvegarder les informations dans la base de données (simulation ici)

            $this->addFlash('success', 'Commande passée avec succès.');
            $session->remove('cart'); // Vider le panier

            return $this->redirectToRoute('home');
        }

        return $this->render('cart/checkout.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/cart/debug', name: 'cart_debug')]
    public function debugCart(SessionInterface $session): Response
    {   
        $cart = $session->get('cart', []);
        return $this->json($cart); // Affiche le contenu du panier pour vérifier les données
    }
    #[Route('/cart/remove/{id}', name: 'cart_remove', methods: ['POST'])]
    public function removeFromCart($id, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);

        // Vérifier si le produit est dans le panier
        if (isset($cart[$id])) {
            // Supprimer le produit du panier
            unset($cart[$id]);
            // Sauvegarder à nouveau le panier mis à jour dans la session
            $session->set('cart', $cart);
        }

        return $this->redirectToRoute('cart_view');
    }


}