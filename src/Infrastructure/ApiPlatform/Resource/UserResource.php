<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Infrastructure\ApiPlatform\State\RegisterUserProcessor;
use App\Infrastructure\ApiPlatform\State\UserStateProvider;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'User',
    operations: [
        new Get(
            security: 'is_granted("ROLE_USER")',
            securityMessage: 'Access denied.',
            provider: UserStateProvider::class,
        ),
        new Post(
            uriTemplate: '/auth/register',
            normalizationContext: ['groups' => ['user:read']],
            denormalizationContext: ['groups' => ['user:register']],
            validationContext: ['groups' => ['user:register']],
            output: UserResource::class,
            processor: RegisterUserProcessor::class,
        ),
    ],
    normalizationContext: ['groups' => ['user:read']],
)]
final class UserResource
{
    #[ApiProperty(identifier: true)]
    #[Groups(['user:read'])]
    public ?string $id = null;

    #[Assert\NotBlank(groups: ['user:register'])]
    #[Assert\Email(groups: ['user:register'])]
    #[Groups(['user:read', 'user:register'])]
    public ?string $email = null;

    #[Assert\NotBlank(groups: ['user:register'])]
    #[Assert\Length(min: 6, max: 50, groups: ['user:register'])]
    #[Groups(['user:register'])]
    public ?string $password = null;

    #[Assert\NotBlank(groups: ['user:register'])]
    #[Assert\Length(max: 100, groups: ['user:register'])]
    #[Groups(['user:read', 'user:register'])]
    public ?string $firstName = null;

    #[Assert\NotBlank(groups: ['user:register'])]
    #[Assert\Length(max: 100, groups: ['user:register'])]
    #[Groups(['user:read', 'user:register'])]
    public ?string $lastName = null;

    #[Assert\NotBlank(groups: ['user:register'])]
    #[Assert\Choice(choices: ['ROLE_CANDIDATE', 'ROLE_RECRUITER'], groups: ['user:register'])]
    #[Groups(['user:register'])]
    public ?string $role = null;

    #[Groups(['user:read'])]
    public ?string $fullName = null;

    /** @var list<string> */
    #[Groups(['user:read'])]
    public array $roles = [];

    #[Groups(['user:read'])]
    public ?\DateTimeImmutable $createdAt = null;

    #[Groups(['user:read'])]
    public ?string $token = null;
}
