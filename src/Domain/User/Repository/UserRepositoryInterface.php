<?php

declare(strict_types=1);

namespace App\Domain\User\Repository;

use App\Domain\User\Entity\User;
use App\Domain\User\Exception\UserNotFoundException;

interface UserRepositoryInterface
{
    public function save(User $user): void;

    public function remove(User $user): void;

    /**
     * @throws UserNotFoundException
     */
    public function getById(string $id): User;

    /**
     * @throws UserNotFoundException
     */
    public function getByEmail(string $email): User;

    public function existsByEmail(string $email): bool;
}
