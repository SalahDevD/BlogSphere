<?php

namespace App\Repository;

use App\Entity\CommentReaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CommentReactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommentReaction::class);
    }

    public function findUserReactionForComment(int $userId, int $commentId): ?CommentReaction
    {
        return $this->createQueryBuilder('cr')
            ->where('cr.user = :userId')
            ->andWhere('cr.comment = :commentId')
            ->setParameter('userId', $userId)
            ->setParameter('commentId', $commentId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countLikesForComment(int $commentId): int
    {
        return (int) $this->createQueryBuilder('cr')
            ->select('COUNT(cr.id)')
            ->where('cr.comment = :commentId')
            ->andWhere('cr.isLike = true')
            ->setParameter('commentId', $commentId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countDislikesForComment(int $commentId): int
    {
        return (int) $this->createQueryBuilder('cr')
            ->select('COUNT(cr.id)')
            ->where('cr.comment = :commentId')
            ->andWhere('cr.isLike = false')
            ->setParameter('commentId', $commentId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
