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

    public function findBySearchTerm(string $term)
    {
        return $this->createQueryBuilder('u')
            ->where('u.name LIKE :term OR u.email LIKE :term OR u.firstName LIKE :term OR u.lastName LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('u.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByRole(string $role)
    {
        // Get all users and filter by role in PHP since DQL doesn't support JSON_CONTAINS
        $allUsers = $this->findAll();
        $result = [];
        
        foreach ($allUsers as $user) {
            if (in_array($role, $user->getRoles())) {
                $result[] = $user;
            }
        }
        
        return $result;
    }
}
