<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Job\Entity\Job;
use App\Domain\Job\Enum\JobStatus;
use App\Domain\Job\Exception\JobNotFoundException;
use App\Domain\Job\Repository\JobRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineJobRepository implements JobRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function save(Job $job): void
    {
        $this->em->persist($job);
        $this->em->flush();
    }

    public function remove(Job $job): void
    {
        $this->em->remove($job);
        $this->em->flush();
    }

    public function getById(string $id): Job
    {
        $job = $this->em->find(Job::class, $id);

        if (null === $job) {
            throw JobNotFoundException::byId($id);
        }

        return $job;
    }

    public function findById(string $id): ?Job
    {
        return $this->em->find(Job::class, $id);
    }

    public function findByRecruiterId(string $recruiterId): array
    {
        return $this->em
            ->getRepository(Job::class)
            ->findBy(['recruiterId' => $recruiterId], ['createdAt' => 'DESC']);
    }

    public function findPublished(?int $limit = null, ?int $offset = null): array
    {
        $qb = $this->em
            ->getRepository(Job::class)
            ->createQueryBuilder('j')
            ->where('j.status = :status')
            ->setParameter('status', JobStatus::PUBLISHED->value)
            ->orderBy('j.createdAt', 'DESC');

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }
        if (null !== $offset) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    public function findByStatus(JobStatus $status): array
    {
        return $this->em
            ->getRepository(Job::class)
            ->findBy(['status' => $status->value], ['createdAt' => 'DESC']);
    }
}
