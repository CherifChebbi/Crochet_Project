<?php
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

        // Récupérer le panier existant
        $cart = $session->get('cart', []);

        // Ajouter le produit si il n'est pas déjà dans le panier
        if (!isset($cart[$id])) {
            $cart[$id] = [
                'product' => [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'price' => $product->getPrice(),
                    'media' => $product->getMedia()->toArray(), // Ajouter les médias
                ]
            ];
        }

        // Sauvegarder dans la session
        $session->set('cart', $cart);

        // Retourner la réponse JSON avec le nombre d'articles dans le panier
        return $this->json([
            'message' => 'Produit ajouté au panier',
            'cartCount' => count($cart),
        ]);
    }

    #[Route('/cart', name: 'cart_view', methods: ['GET'])]
    public function viewCart(SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        
        // Calculer le total
        $total = $this->calculateTotal($cart);

        return $this->render('cart/view.html.twig', [
            'cart' => $cart,
            'total' => $total
        ]);
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

    private function calculateTotal($cart)
    {
        $total = 0;
        foreach ($cart as $item) {
            // Calculer le total sans tenir compte de la quantité
            $total += $item['product']['price'];     // Pas besoin de multiplier par la quantité, un produit = un item
        }
        return $total;
    }
}
