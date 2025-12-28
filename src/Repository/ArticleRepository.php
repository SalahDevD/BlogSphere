<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 *
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * 🔥 Trouver les articles "populaires" pour le carousel
     * (version simple : les articles approuvés les plus récents)
     */
    public function findPopularArticles(int $limit = 5): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.validationStatus = :status')
            ->setParameter('status', 'approved')
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouver les articles approuvés d'un auteur
     */
    public function findApprovedByAuthor($author): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.author = :author')
            ->andWhere('a.validationStatus = :status')
            ->setParameter('author', $author)
            ->setParameter('status', 'approved')
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compter les articles approuvés d'un auteur
     */
    public function countApprovedByAuthor($author): int
    {
        return $this->count([
            'author' => $author,
            'validationStatus' => 'approved',
        ]);
    }
}
