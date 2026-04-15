<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\User\Entity\User;
use App\Domain\User\Exception\UserNotFoundException;
use App\Domain\User\Repository\UserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function save(User $user): void
    {
        $this->em->persist($user);
        $this->em->flush();
    }

    public function remove(User $user): void
    {
        $this->em->remove($user);
        $this->em->flush();
    }

    public function getById(string $id): User
    {
        $user = $this->em->find(User::class, $id);

        if (null === $user) {
            throw UserNotFoundException::byId($id);
        }

        return $user;
    }

    public function getByEmail(string $email): User
    {
        $user = $this->em
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);

        if (null === $user) {
            throw UserNotFoundException::byEmail($email);
        }

        return $user;
    }

    public function existsByEmail(string $email): bool
    {
        $result = $this->em
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);

        return null !== $result;
    }
}
