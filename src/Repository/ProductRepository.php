<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }
    public function findWithMedia($id)
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.media', 'm')
            ->addSelect('m')
            ->where('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllSortedAndFiltered(?string $searchQuery = null, ?bool $filterByAvailability = null, ?string $sortByDate = 'desc'): array
    {
        $qb = $this->createQueryBuilder('p');
        
        // Appliquer le filtre par availability
        if ($filterByAvailability !== null) {
            $qb->andWhere('p.availability = :availability')
                ->setParameter('availability', $filterByAvailability);
        }

        // Appliquer la recherche par  name ou ID
        if ($searchQuery !== null && $searchQuery !== '') {
            // Vérifier si la chaîne de recherche peut être convertie en entier
            if (ctype_digit($searchQuery)) {
                // Si oui, comparer l'ID avec la valeur convertie
                $qb->andWhere($qb->expr()->orX(
                    $qb->expr()->like('p.name', ':searchQuery'),
                    $qb->expr()->eq('p.id', ':searchQueryId')
                ))
                ->setParameter('searchQuery', '%' . $searchQuery . '%')
                ->setParameter('searchQueryId', (int) $searchQuery);
            } else {
                // Sinon, rechercher uniquement par customerName
                $qb->andWhere($qb->expr()->like('p.name', ':searchQuery'))
                    ->setParameter('searchQuery', '%' . $searchQuery . '%');
            }   
        }

        // Tri par date d'ajout
        $qb->orderBy('p.productDate', $sortByDate === 'asc' ? 'ASC' : 'DESC');

        return $qb->getQuery()->getResult();
    }
    /**
     * Retourne le nombre total de produits.
     */
    public function countTotalProducts(): int
    {
        return $this->count([]);
    }
    /**
     * Retourne le nombre de produits disponibles ou non disponibles.
     */
    public function countProductsByAvailability(bool $availability): int
    {
        return $this->count(['availability' => $availability]);
    }

    /**
     * Retourne le nombre de produits ajoutés dans une période donnée.
     */
    public function countProductsAddedBetween(\DateTimeInterface $startDate, \DateTimeInterface $endDate): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.productDate BETWEEN :start AND :end')
            ->setParameter('start', $startDate->format('Y-m-d 00:00:00'))
            ->setParameter('end', $endDate->format('Y-m-d 23:59:59'))
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Retourne le nombre de produits ajoutés ce mois-ci.
     */
    public function countProductsAddedThisMonth(): int
    {
        $startDate = new \DateTime('first day of this month');
        $endDate = new \DateTime('last day of this month');

        return $this->countProductsAddedBetween($startDate, $endDate);
    }

    

}
