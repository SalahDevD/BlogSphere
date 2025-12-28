<?php

namespace App\Repository;

use App\Entity\Reaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reaction>
 */
class ReactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reaction::class);
    }

    /**
     * Trouver la réaction d'un utilisateur pour un article
     */
    public function findUserReactionForArticle(int $userId, int $articleId): ?Reaction
    {
        return $this->createQueryBuilder('r')
            ->where('r.user = :userId')
            ->andWhere('r.article = :articleId')
            ->setParameter('userId', $userId)
            ->setParameter('articleId', $articleId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Compter les likes pour un article
     */
    public function countLikesForArticle(int $articleId): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.article = :articleId')
            ->andWhere('r.isLike = true')
            ->setParameter('articleId', $articleId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compter les dislikes pour un article
     */
    public function countDislikesForArticle(int $articleId): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.article = :articleId')
            ->andWhere('r.isLike = false')
            ->setParameter('articleId', $articleId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouver la réaction d'un utilisateur pour un commentaire
     */
    public function findUserReactionForComment(int $userId, int $commentId): ?Reaction
    {
        return $this->createQueryBuilder('r')
            ->where('r.user = :userId')
            ->andWhere('r.comment = :commentId')
            ->setParameter('userId', $userId)
            ->setParameter('commentId', $commentId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Compter les likes pour un commentaire
     */
    public function countLikesForComment(int $commentId): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.comment = :commentId')
            ->andWhere('r.isLike = true')
            ->setParameter('commentId', $commentId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compter les dislikes pour un commentaire
     */
    public function countDislikesForComment(int $commentId): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.comment = :commentId')
            ->andWhere('r.isLike = false')
            ->setParameter('commentId', $commentId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
