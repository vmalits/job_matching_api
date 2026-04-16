<?php

declare(strict_types=1);

namespace App\Domain\Job\Repository;

use App\Domain\Job\Entity\Job;
use App\Domain\Job\Enum\JobStatus;

interface JobRepositoryInterface
{
    public function save(Job $job): void;

    public function remove(Job $job): void;

    public function getById(string $id): Job;

    public function findById(string $id): ?Job;

    /**
     * @return list<Job>
     */
    public function findByRecruiterId(string $recruiterId): array;

    /**
     * @return list<Job>
     */
    public function findPublished(?int $limit = null, ?int $offset = null): array;

    /**
     * @return list<Job>
     */
    public function findByStatus(JobStatus $status): array;
}
