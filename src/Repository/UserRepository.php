<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
* @implements PasswordUpgraderInterface<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }
    public function findByRole(string $role): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('role', '%' . $role . '%')
            ->getQuery()
            ->getResult();
    }
    public function findBySearchAndRole(?string $searchQuery, ?string $roleFilter): array
    {
        $qb = $this->createQueryBuilder('u');

        // Recherche par firstName, lastName ou id
        if ($searchQuery !== null && $searchQuery !== '') {
            // Vérifier si la chaîne de recherche peut être convertie en entier (pour l'id)
            if (ctype_digit($searchQuery)) {
                // Si oui, comparer l'ID avec la valeur convertie
                $qb->andWhere($qb->expr()->orX(
                    $qb->expr()->like('u.firstName', ':searchQuery'),
                    $qb->expr()->like('u.lastName', ':searchQuery'),
                    $qb->expr()->eq('u.id', ':searchQueryId')
                ))
                ->setParameter('searchQuery', '%' . $searchQuery . '%')
                ->setParameter('searchQueryId', (int) $searchQuery);
            } else {
                // Sinon, rechercher uniquement par firstName et lastName
                $qb->andWhere($qb->expr()->orX(
                    $qb->expr()->like('u.firstName', ':searchQuery'),
                    $qb->expr()->like('u.lastName', ':searchQuery')
                ))
                ->setParameter('searchQuery', '%' . $searchQuery . '%');
            }
        }

         // Filtre par rôle
        if ($roleFilter) {
        // Convertir le tableau de rôles en chaîne JSON et vérifier si le rôle est présent
        $qb->andWhere($qb->expr()->like('u.roles', ':roleFilter'))
            ->setParameter('roleFilter', '%' . json_encode($roleFilter) . '%');
        }


        return $qb->getQuery()->getResult();
    }

     /**
     * Compte le nombre d'utilisateurs ayant un rôle spécifique.
     */
    public function countUsersByRole(string $role): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%' . json_encode($role) . '%')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
