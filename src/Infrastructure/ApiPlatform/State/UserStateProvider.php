<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Infrastructure\ApiPlatform\Resource\UserResource;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements ProviderInterface<UserResource>
 */
final readonly class UserStateProvider implements ProviderInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private Security $security,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?UserResource
    {
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

        if ($operation instanceof Get && isset($uriVariables['id'])) {
            // Users can only see their own profile
            if ($currentUser->getId() !== $uriVariables['id']) {
                return null;
            }

            $user = $this->userRepository->getById($uriVariables['id']);

            return $this->toResource($user);
        }

        return $this->toResource($currentUser);
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

        return $resource;
    }
}
