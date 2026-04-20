<?php

declare(strict_types=1);

namespace App\Domain\CandidateProfile\Repository;

use App\Domain\CandidateProfile\Entity\CandidateProfile;

interface CandidateProfileRepositoryInterface
{
    public function save(CandidateProfile $profile): void;

    public function remove(CandidateProfile $profile): void;

    public function getByUserId(string $userId): CandidateProfile;

    public function findByUserId(string $userId): ?CandidateProfile;

    public function findById(string $id): ?CandidateProfile;

    public function existsByUserId(string $userId): bool;
}
