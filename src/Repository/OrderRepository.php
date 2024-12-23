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

    // You can add more custom methods as needed
}
