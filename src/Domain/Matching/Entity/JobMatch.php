<?php

declare(strict_types=1);

namespace App\Domain\Matching\Entity;

use App\Domain\Matching\Enum\MatchStatus;
use Symfony\Component\Uid\Uuid;

class JobMatch
{
    private string $id;
    private string $candidateProfileId;
    private string $jobId;
    private int $score;
    private MatchStatus $status;
    private float $skillsMatch;
    private float $salaryMatch;
    private float $locationMatch;
    private float $experienceMatch;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        string $candidateProfileId,
        string $jobId,
        int $score,
        float $skillsMatch,
        float $salaryMatch,
        float $locationMatch,
        float $experienceMatch,
    ) {
        $this->id = Uuid::v7()->toRfc4122();
        $this->candidateProfileId = $candidateProfileId;
        $this->jobId = $jobId;
        $this->score = $score;
        $this->skillsMatch = $skillsMatch;
        $this->salaryMatch = $salaryMatch;
        $this->locationMatch = $locationMatch;
        $this->experienceMatch = $experienceMatch;
        $this->status = MatchStatus::PENDING;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCandidateProfileId(): string
    {
        return $this->candidateProfileId;
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function getStatus(): MatchStatus
    {
        return $this->status;
    }

    public function getSkillsMatch(): float
    {
        return $this->skillsMatch;
    }

    public function getSalaryMatch(): float
    {
        return $this->salaryMatch;
    }

    public function getLocationMatch(): float
    {
        return $this->locationMatch;
    }

    public function getExperienceMatch(): float
    {
        return $this->experienceMatch;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function accept(): static
    {
        if (MatchStatus::PENDING !== $this->status && MatchStatus::VIEWED !== $this->status) {
            throw new \LogicException('Only pending or viewed matches can be accepted.');
        }
        $this->status = MatchStatus::ACCEPTED;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function reject(): static
    {
        if (MatchStatus::PENDING !== $this->status && MatchStatus::VIEWED !== $this->status) {
            throw new \LogicException('Only pending or viewed matches can be rejected.');
        }
        $this->status = MatchStatus::REJECTED;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function markViewed(): static
    {
        if (MatchStatus::PENDING === $this->status) {
            $this->status = MatchStatus::VIEWED;
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }
}
