<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Infrastructure\ApiPlatform\State\CandidateProfileProcessor;
use App\Infrastructure\ApiPlatform\State\CandidateProfileProvider;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'CandidateProfile',
    operations: [
        new Get(
            provider: CandidateProfileProvider::class,
        ),
        new Post(
            normalizationContext: ['groups' => ['profile:read']],
            denormalizationContext: ['groups' => ['profile:write']],
            security: 'is_granted("ROLE_CANDIDATE")',
            securityMessage: 'Only candidates can create profiles.',
            validationContext: ['groups' => ['profile:write']],
            processor: CandidateProfileProcessor::class,
        ),
        new Patch(
            provider: CandidateProfileProvider::class,
            normalizationContext: ['groups' => ['profile:read']],
            denormalizationContext: ['groups' => ['profile:write']],
            security: 'is_granted("ROLE_CANDIDATE")',
            securityMessage: 'Only candidates can update profiles.',
            validationContext: ['groups' => ['profile:write']],
            processor: CandidateProfileProcessor::class,
        ),
    ],
    normalizationContext: ['groups' => ['profile:read']],
    forceEager: false,
)]
final class CandidateProfileResource
{
    #[ApiProperty(identifier: true)]
    #[Groups(['profile:read'])]
    public ?string $id = null;

    #[Groups(['profile:read'])]
    public ?string $userId = null;

    #[Assert\NotBlank(groups: ['profile:write'])]
    #[Assert\Length(max: 200, groups: ['profile:write'])]
    #[Groups(['profile:read', 'profile:write'])]
    public ?string $title = null;

    #[Assert\NotBlank(groups: ['profile:write'])]
    #[Groups(['profile:read', 'profile:write'])]
    public ?string $bio = null;

    #[Assert\NotBlank(groups: ['profile:write'])]
    #[Assert\Length(max: 200, groups: ['profile:write'])]
    #[Groups(['profile:read', 'profile:write'])]
    public ?string $location = null;

    #[Assert\NotBlank(groups: ['profile:write'])]
    #[Assert\Range(min: 0, max: 50, groups: ['profile:write'])]
    #[Groups(['profile:read', 'profile:write'])]
    public ?int $experienceYears = null;

    /** @var list<string> */
    #[Groups(['profile:read', 'profile:write'])]
    public array $skills = [];

    #[Assert\Range(min: 0, groups: ['profile:write'])]
    #[Groups(['profile:read', 'profile:write'])]
    public ?int $salaryMin = null;

    #[Assert\Range(min: 0, groups: ['profile:write'])]
    #[Groups(['profile:read', 'profile:write'])]
    public ?int $salaryMax = null;

    #[Groups(['profile:read', 'profile:write'])]
    public ?string $resumeUrl = null;

    #[Groups(['profile:read'])]
    public ?\DateTimeImmutable $createdAt = null;

    #[Groups(['profile:read'])]
    public ?string $userEmail = null;
}
