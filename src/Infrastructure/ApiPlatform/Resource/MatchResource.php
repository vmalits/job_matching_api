<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use App\Infrastructure\ApiPlatform\State\MatchProcessor;
use App\Infrastructure\ApiPlatform\State\MatchProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Get(
            provider: MatchProvider::class,
        ),
        new GetCollection(
            provider: MatchProvider::class,
        ),
        new Patch(
            normalizationContext: ['groups' => ['match:read']],
            denormalizationContext: ['groups' => ['match:update']],
            processor: MatchProcessor::class,
        ),
    ],
    normalizationContext: ['groups' => ['match:read']],
    forceEager: false,
)]
final class MatchResource
{
    #[ApiProperty(identifier: true)]
    #[Groups(['match:read'])]
    public ?string $id = null;

    #[Groups(['match:read'])]
    public ?string $candidateProfileId = null;

    #[Groups(['match:read'])]
    public ?string $jobId = null;

    #[Groups(['match:read'])]
    public ?int $score = null;

    #[Groups(['match:read'])]
    public ?string $status = null;

    #[Groups(['match:read'])]
    public ?float $skillsMatch = null;

    #[Groups(['match:read'])]
    public ?float $salaryMatch = null;

    #[Groups(['match:read'])]
    public ?float $locationMatch = null;

    #[Groups(['match:read'])]
    public ?float $experienceMatch = null;

    #[Groups(['match:read'])]
    public ?\DateTimeImmutable $createdAt = null;

    /** Candidate sees: job details */
    #[Groups(['match:read'])]
    public ?string $jobTitle = null;

    #[Groups(['match:read'])]
    public ?string $jobCompanyName = null;

    #[Groups(['match:read'])]
    public ?string $jobLocation = null;

    #[Groups(['match:read'])]
    public ?string $jobEmploymentType = null;

    /** Recruiter sees: candidate email */
    #[Groups(['match:read'])]
    public ?string $candidateEmail = null;

    #[Groups(['match:update'])]
    public ?string $action = null;
}
