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
    public function back(OrderRepository $orderRepository,ProductRepository $productRepository, Request $request): Response
    {
        // Récupérer les paramètres de tri, de filtrage et de recherche
        $sortByDate = $request->query->get('sort_by_date', 'desc'); // Par défaut, tri par date décroissante
        $filterByVerified = $request->query->get('filter_by_verified');
        $searchQuery = $request->query->get('search_query'); // Nouveau paramètre de recherche

        // Convertir la chaîne vide en null pour le filtre
        if ($filterByVerified === '') {
            $filterByVerified = null;
        }

        // Utiliser les méthodes du repository pour trier, filtrer et rechercher
        $orders = $orderRepository->findAllSortedAndFiltered($sortByDate, $filterByVerified, $searchQuery);

        // Récupérer les statistiques
        $todayOrdersCount = $orderRepository->countTodayOrders();
        $notVerifiedOrdersCount = $orderRepository->countNotVerifiedOrders();
        $mostSoldProduct = $orderRepository->findMostSoldProduct($productRepository);
        $productsSoldThisMonth = $orderRepository->countProductsSoldThisMonth();

        return $this->render('back/orders.html.twig', [
            'orders' => $orders,
            'sort_by_date' => $sortByDate,
            'filter_by_verified' => $filterByVerified,
            'search_query' => $searchQuery,
            'today_orders_count' => $todayOrdersCount,
            'not_verified_orders_count' => $notVerifiedOrdersCount,
            'most_sold_product' => $mostSoldProduct,
            'products_sold_this_month' => $productsSoldThisMonth,
            
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
        $order->setOrderDate(new \DateTime()); // Ajouter la date actuelle

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

    #[Route('/order/update/{id}', name: 'app_order_edit')]
    public function updateOrder(int $id, Request $request, OrderRepository $orderRepository, EntityManagerInterface $entityManager): Response
    {
        // Retrieve the order from the database using the provided ID
        $order = $orderRepository->find($id);

        // If the order does not exist, throw a 404 error
        if (!$order) {
            throw $this->createNotFoundException('Order not found');
        }

        // Check if the form has been submitted and handle the update manually
        if ($request->isMethod('POST')) {
            // Retrieve data from the form or query parameters
            $customerName = $request->request->get('customerName');
            $customerAddress = $request->request->get('customerAddress');
            $customerPhone = $request->request->get('customerPhone');
            $totalAmount = $request->request->get('totalAmount');
            $productIds = $request->request->get('productIds');
            $isVerified = $request->request->get('isVerified') === 'on'; // Checkbox handling for boolean values

            // Manually set the new values to the order object
            $order->setCustomerName($customerName);
            $order->setCustomerAddress($customerAddress);
            $order->setCustomerPhone($customerPhone);
            $order->setTotalAmount((float)$totalAmount);
            $order->setProductIds(explode('-', $productIds)); // Assuming productIds are passed as a hyphen-separated string
            $order->setIsVerified($isVerified);

            // Persist the changes to the database
            $entityManager->flush();

            // Redirect or return a success message
            $this->addFlash('success', 'Order updated successfully');
            return $this->redirectToRoute('app_dashboard_orders');  // Redirect to a list of orders or another page
        }

        // Render the form manually, passing the current order data as default values
        return $this->render('order/edit.html.twig', [
            'order' => $order,
        ]);
    }


    
}
