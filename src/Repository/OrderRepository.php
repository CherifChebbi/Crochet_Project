<?php
// src/Repository/OrderRepository.php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    // Example of a custom method to find orders by customer
    public function findOrdersByCustomer($customerId)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.customer = :customer')
            ->setParameter('customer', $customerId)
            ->getQuery()
            ->getResult();
    }

    // OrderRepository.php

    public function findAllSortedAndFiltered(string $sortByDate = 'desc', ?bool $filterByVerified = null, ?string $searchQuery = null): array
    {
        $qb = $this->createQueryBuilder('o');

        // Appliquer le filtre par isVerified si nécessaire
        if ($filterByVerified !== null) {
            $qb->andWhere('o.isVerified = :isVerified')
                ->setParameter('isVerified', $filterByVerified);
        }

        // Appliquer la recherche par customer name ou ID
        if ($searchQuery !== null && $searchQuery !== '') {
            // Vérifier si la chaîne de recherche peut être convertie en entier
            if (ctype_digit($searchQuery)) {
                // Si oui, comparer l'ID avec la valeur convertie
                $qb->andWhere($qb->expr()->orX(
                    $qb->expr()->like('o.customerName', ':searchQuery'),
                    $qb->expr()->eq('o.id', ':searchQueryId')
                ))
                ->setParameter('searchQuery', '%' . $searchQuery . '%')
                ->setParameter('searchQueryId', (int) $searchQuery);
            } else {
                // Sinon, rechercher uniquement par customerName
                $qb->andWhere($qb->expr()->like('o.customerName', ':searchQuery'))
                    ->setParameter('searchQuery', '%' . $searchQuery . '%');
            }   
        }

        // Appliquer le tri par date
        $qb->orderBy('o.orderDate', $sortByDate === 'asc' ? 'ASC' : 'DESC');

        return $qb->getQuery()->getResult();
    }
    
    public function countTodayOrders(): int
    {
        $today = new \DateTime('today');
        return $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->where('o.orderDate >= :today')
            ->setParameter('today', $today)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countNotVerifiedOrders(): int
    {
        return $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->where('o.isVerified = false')
            ->getQuery()
            ->getSingleScalarResult();
    }
    
    public function findMostSoldProduct(ProductRepository $productRepository): ?array
    {
        // Récupérer toutes les commandes
        $orders = $this->createQueryBuilder('o')
            ->select('o.productIds')
            ->getQuery()
            ->getResult();

        // Tableau pour compter les occurrences de chaque produit
        $productCounts = [];

        // Parcourir toutes les commandes
        foreach ($orders as $order) {
            // Extraire les IDs des produits de la chaîne
            $productIds = explode('-', $order['productIds']);

            // Compter les occurrences de chaque produit
            foreach ($productIds as $productId) {
                if (!empty($productId)) {
                    if (isset($productCounts[$productId])) {
                        $productCounts[$productId]++;
                    } else {
                        $productCounts[$productId] = 1;
                    }
                }
            }
        }

        // Trouver le produit le plus vendu
        if (!empty($productCounts)) {
            $mostSoldProductId = array_search(max($productCounts), $productCounts);
            $mostSoldProductCount = $productCounts[$mostSoldProductId];

            // Récupérer le nom du produit à partir de son ID
            $product = $productRepository->find($mostSoldProductId);
            $productName = $product ? $product->getName() : 'Produit inconnu';

            // Retourner le produit le plus vendu, son nom et son nombre de ventes
            return [
                'id' => $mostSoldProductId,
                'name' => $productName,
                'sales' => $mostSoldProductCount,
            ];
        }

        // Retourner null si aucun produit n'a été vendu
        return null;
    }

    public function countProductsSoldThisMonth(): int
    {
        $startOfMonth = new \DateTime('first day of this month');
        $endOfMonth = new \DateTime('last day of this month');

        // Récupérer toutes les commandes ce mois-ci
        $orders = $this->createQueryBuilder('o')
            ->select('o.productIds')
            ->where('o.orderDate >= :startOfMonth')
            ->andWhere('o.orderDate <= :endOfMonth')
            ->setParameter('startOfMonth', $startOfMonth)
            ->setParameter('endOfMonth', $endOfMonth)
            ->getQuery()
            ->getResult();

        // Compter le nombre total de produits vendus
        $totalProductsSold = 0;

        foreach ($orders as $order) {
            $productIds = explode('-', $order['productIds']);
            $totalProductsSold += count(array_filter($productIds)); // Ignorer les valeurs vides
        }

        return $totalProductsSold;
    }
}
