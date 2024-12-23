<?php
namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderType;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

class OrderController extends AbstractController
{
    #[Route('/dashboard/orders', name: 'app_dashboard_orders', methods: ['GET'])]
    public function back(OrderRepository $orderRepository): Response
    {
        return $this->render('back/orders.html.twig', [
            'orders' => $orderRepository->findAll(),
        ]);
    }
    #[Route('/order/new', name: 'order_new')]
    public function new(Request $request): Response
    {
        // Récupérer les données du panier depuis la session
        $session = $this->get('session');
        $cart = $session->get('cart', []); // Structure : ['id' => ['product' => Product], ...]

        // Vérifier que le panier n'est pas vide
        if (empty($cart)) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('cart_view'); // Redirection vers la page du panier
        }

        // Calculer le total des produits (sans frais de livraison)
        $total = array_reduce($cart, function ($sum, $item) {
            return $sum + $item['product']['price'];
        }, 0);

        // Ajouter les frais de livraison de 7 DT
        $deliveryFee = 7;
        $totalWithDelivery = $total + $deliveryFee;

        // Créer une nouvelle commande
        $order = new Order();
        $order->setTotalAmount($totalWithDelivery); // Utiliser le total avec les frais de livraison

        // Ajouter les IDs des produits à l'ordre
        $productIds = array_map(fn($item) => $item['product']['id'], $cart);
        $order->setProductIds($productIds);

        // Créer le formulaire pour saisir les informations de livraison
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarder la commande
            $em = $this->getDoctrine()->getManager();
            $em->persist($order);
            $em->flush();

            // Vider le panier après la validation
            $session->remove('cart');

            // Confirmation et redirection
            $this->addFlash('success', 'Votre commande a été passée avec succès.');
            return $this->redirectToRoute('app_product_index'); // Redirection vers la page d'accueil ou des produits
        }

        return $this->render('order/new.html.twig', [
            'form' => $form->createView(),
            'cart' => $cart,
            'total' => $totalWithDelivery, // Afficher le total avec les frais de livraison
        ]);
    }
    //---------DELETE SIMPLE back----------------
    #[Route('/order/{id}/delete', name: 'app_order_delete_simple')]
    public function deletePays( Order $order, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($order);
        $entityManager->flush();

        return $this->redirectToRoute('app_dashboard_orders');
    }
    //---------SHOW back-----------
#[Route('/order/{id}', name: 'app_order_show', methods: ['GET'])]
public function show(Order $order): Response
{
    return $this->render('order/show.html.twig', [
        'order' => $order,
    ]);
}


    
}
