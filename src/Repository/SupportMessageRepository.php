<?php

namespace App\Repository;

use App\Entity\SupportMessage;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SupportMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SupportMessage::class);
    }

    public function findConversationForUser(User $user): array
    {
        return $this->createQueryBuilder('sm')
            ->where('sm.sender = :user')
            ->orWhere('sm.receiver = :user')
            ->setParameter('user', $user)
            ->orderBy('sm.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findConversationsByUser(User $user): array
    {
        return $this->createQueryBuilder('sm')
            ->where('sm.sender = :user')
            ->andWhere('sm.parentMessage IS NULL')
            ->setParameter('user', $user)
            ->orderBy('sm.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findConversationThread(SupportMessage $message): array
    {
        $rootMessage = $message->getParentMessage() ?? $message;
        
        return $this->createQueryBuilder('sm')
            ->where('sm.id = :rootId')
            ->orWhere('sm.parentMessage = :rootId')
            ->setParameter('rootId', $rootMessage->getId())
            ->orderBy('sm.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countUnreadMessagesForUser(User $user): int
    {
        return (int) $this->createQueryBuilder('sm')
            ->select('COUNT(sm.id)')
            ->where('sm.receiver = :user')
            ->andWhere('sm.isRead = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function markAsRead(User $user): void
    {
        $this->createQueryBuilder('sm')
            ->update()
            ->set('sm.isRead', true)
            ->where('sm.receiver = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }
}
