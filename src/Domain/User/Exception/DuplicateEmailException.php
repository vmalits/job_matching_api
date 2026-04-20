<?php

declare(strict_types=1);

namespace App\Domain\User\Exception;

final class DuplicateEmailException extends \RuntimeException
{
    public static function withEmail(string $email): self
    {
        return new self(\sprintf('Email "%s" already exists.', $email));
    }
}
