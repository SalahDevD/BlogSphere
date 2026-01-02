<?php

namespace App\Repository;

use App\Entity\UserImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserImage>
 */
class UserImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserImage::class);
    }

    public function findProfileImageByUser($userId)
    {
        return $this->findOneBy(['user' => $userId, 'isProfile' => true]);
    }

    public function findAllByUser($userId)
    {
        return $this->findBy(['user' => $userId], ['uploadedAt' => 'DESC']);
    }
}
