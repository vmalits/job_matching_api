<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Infrastructure\ApiPlatform\State\JobProcessor;
use App\Infrastructure\ApiPlatform\State\JobProvider;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(
            provider: JobProvider::class,
        ),
        new GetCollection(
            provider: JobProvider::class,
        ),
        new Post(
            normalizationContext: ['groups' => ['job:read']],
            denormalizationContext: ['groups' => ['job:write']],
            security: 'is_granted("ROLE_RECRUITER")',
            securityMessage: 'Only recruiters can create jobs.',
            validationContext: ['groups' => ['job:write']],
            processor: JobProcessor::class,
        ),
        new Patch(
            normalizationContext: ['groups' => ['job:read']],
            denormalizationContext: ['groups' => ['job:write']],
            security: 'is_granted("ROLE_RECRUITER")',
            securityMessage: 'Only recruiters can update jobs.',
            validationContext: ['groups' => ['job:write']],
            processor: JobProcessor::class,
        ),
    ],
    normalizationContext: ['groups' => ['job:read']],
    forceEager: false,
)]
final class JobResource
{
    #[ApiProperty(identifier: true)]
    #[Groups(['job:read'])]
    public ?string $id = null;

    #[Groups(['job:read'])]
    public ?string $recruiterId = null;

    #[Assert\NotBlank(groups: ['job:write'])]
    #[Assert\Length(max: 200, groups: ['job:write'])]
    #[Groups(['job:read', 'job:write'])]
    public ?string $title = null;

    #[Assert\NotBlank(groups: ['job:write'])]
    #[Groups(['job:read', 'job:write'])]
    public ?string $description = null;

    #[Assert\NotBlank(groups: ['job:write'])]
    #[Assert\Length(max: 200, groups: ['job:write'])]
    #[Groups(['job:read', 'job:write'])]
    public ?string $companyName = null;

    #[Assert\NotBlank(groups: ['job:write'])]
    #[Assert\Length(max: 200, groups: ['job:write'])]
    #[Groups(['job:read', 'job:write'])]
    public ?string $location = null;

    #[Assert\NotBlank(groups: ['job:write'])]
    #[Assert\Choice(choices: ['full_time', 'part_time', 'contract', 'internship', 'remote'], groups: ['job:write'])]
    #[Groups(['job:read', 'job:write'])]
    public ?string $employmentType = null;

    #[Groups(['job:read', 'job:write'])]
    public ?string $status = null;

    /** @var list<string> */
    #[Groups(['job:read', 'job:write'])]
    public array $skills = [];

    #[Assert\Range(min: 0, groups: ['job:write'])]
    #[Groups(['job:read', 'job:write'])]
    public ?int $salaryMin = null;

    #[Assert\Range(min: 0, groups: ['job:write'])]
    #[Groups(['job:read', 'job:write'])]
    public ?int $salaryMax = null;

    #[Groups(['job:read', 'job:write'])]
    public bool $salaryVisible = false;

    #[Groups(['job:read'])]
    public ?\DateTimeImmutable $createdAt = null;

    #[Groups(['job:read'])]
    public ?string $recruiterEmail = null;
}
