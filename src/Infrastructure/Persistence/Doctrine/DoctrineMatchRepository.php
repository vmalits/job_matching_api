<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Matching\Entity\JobMatch;
use App\Domain\Matching\Exception\JobMatchNotFoundException;
use App\Domain\Matching\Repository\MatchRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineMatchRepository implements MatchRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function save(JobMatch $match): void
    {
        $this->em->persist($match);
        $this->em->flush();
    }

    public function getById(string $id): JobMatch
    {
        $match = $this->em->find(JobMatch::class, $id);

        if (null === $match) {
            throw JobMatchNotFoundException::byId($id);
        }

        return $match;
    }

    public function findByCandidateProfileId(string $candidateProfileId): array
    {
        return $this->em
            ->getRepository(JobMatch::class)
            ->findBy(['candidateProfileId' => $candidateProfileId], ['score' => 'DESC']);
    }

    public function findByJobId(string $jobId): array
    {
        return $this->em
            ->getRepository(JobMatch::class)
            ->findBy(['jobId' => $jobId], ['score' => 'DESC']);
    }

    public function findByCandidateAndJob(string $candidateProfileId, string $jobId): ?JobMatch
    {
        return $this->em
            ->getRepository(JobMatch::class)
            ->findOneBy([
                'candidateProfileId' => $candidateProfileId,
                'jobId' => $jobId,
            ]);
    }

    public function findTopForCandidate(string $candidateProfileId, int $limit = 10): array
    {
        return $this->em
            ->getRepository(JobMatch::class)
            ->findBy(['candidateProfileId' => $candidateProfileId], ['score' => 'DESC'], $limit);
    }

    public function findTopForJob(string $jobId, int $limit = 10): array
    {
        return $this->em
            ->getRepository(JobMatch::class)
            ->findBy(['jobId' => $jobId], ['score' => 'DESC'], $limit);
    }
}
