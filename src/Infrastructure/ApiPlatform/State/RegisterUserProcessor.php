<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Domain\User\Entity\User;
use App\Domain\User\Enum\Role;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Infrastructure\ApiPlatform\Resource\UserResource;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @implements ProcessorInterface<UserResource, UserResource>
 */
final readonly class RegisterUserProcessor implements ProcessorInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        #[Autowire(service: 'security.user_password_hasher')]
        private UserPasswordHasherInterface $passwordHasher,
        private JWTTokenManagerInterface $jwtManager,
    ) {
    }

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): UserResource {
        /** @var non-empty-string $email */
        $email = $data->email;
        /** @var non-empty-string $password */
        $password = $data->password;
        /** @var non-empty-string $firstName */
        $firstName = $data->firstName;
        /** @var non-empty-string $lastName */
        $lastName = $data->lastName;
        /** @var non-empty-string $roleValue */
        $roleValue = $data->role;

        if ($this->userRepository->existsByEmail($email)) {
            throw new \DomainException('Email already exists.');
        }

        $role = Role::from($roleValue);
        $user = new User(
            email: $email,
            firstName: $firstName,
            lastName: $lastName,
            role: $role,
        );

        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->userRepository->save($user);

        return $this->toResource($user);
    }

    private function toResource(User $user): UserResource
    {
        $resource = new UserResource();
        $resource->id = $user->getId();
        $resource->email = $user->getEmail();
        $resource->firstName = $user->getFirstName();
        $resource->lastName = $user->getLastName();
        $resource->fullName = $user->getFullName();
        $resource->roles = $user->getRoles();
        $resource->createdAt = $user->getCreatedAt();
        $resource->token = $this->jwtManager->create($user);

        return $resource;
    }
}
