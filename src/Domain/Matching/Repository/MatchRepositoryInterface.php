<?php

declare(strict_types=1);

namespace App\Domain\Matching\Repository;

use App\Domain\Matching\Entity\JobMatch;

interface MatchRepositoryInterface
{
    public function save(JobMatch $match): void;

    public function getById(string $id): JobMatch;

    /**
     * @return list<JobMatch>
     */
    public function findByCandidateProfileId(string $candidateProfileId): array;

    /**
     * @return list<JobMatch>
     */
    public function findByJobId(string $jobId): array;

    public function findByCandidateAndJob(string $candidateProfileId, string $jobId): ?JobMatch;

    /**
     * @return list<JobMatch>
     */
    public function findTopForCandidate(string $candidateProfileId, int $limit = 10): array;

    /**
     * @return list<JobMatch>
     */
    public function findTopForJob(string $jobId, int $limit = 10): array;
}
