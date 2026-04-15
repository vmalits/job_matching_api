<?php

declare(strict_types=1);

namespace App\Domain\User\Entity;

use App\Domain\User\Enum\Role;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    private string $id;
    private string $email;
    private ?string $password = null;
    private string $firstName;
    private string $lastName;
    /** @var list<Role> */
    private array $roles;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        string $email,
        string $firstName,
        string $lastName,
        Role $role,
    ) {
        $this->id = Uuid::v7()->toRfc4122();
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->roles = [$role];
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getUserIdentifier(): string
    {
        if ('' === $this->email) {
            throw new \LogicException('User email cannot be empty.');
        }

        return $this->email;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getFullName(): string
    {
        return \sprintf('%s %s', $this->firstName, $this->lastName);
    }

    /**
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = array_map(static fn (Role $role): string => $role->value, $this->roles);
        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
    }

    /**
     * @return list<Role>
     */
    public function getDomainRoles(): array
    {
        return $this->roles;
    }

    public function getPrimaryRole(): Role
    {
        return $this->roles[0];
    }

    public function isCandidate(): bool
    {
        return $this->hasRole(Role::CANDIDATE);
    }

    public function isRecruiter(): bool
    {
        return $this->hasRole(Role::RECRUITER);
    }

    public function hasRole(Role $role): bool
    {
        return \in_array($role, $this->roles, true);
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $hashedPassword): static
    {
        $this->password = $hashedPassword;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }
}
