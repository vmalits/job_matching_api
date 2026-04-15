<?php

declare(strict_types=1);

namespace App\Domain\CandidateProfile\Exception;

final class CandidateProfileNotFoundException extends \RuntimeException
{
    public static function byUserId(string $userId): self
    {
        return new self(\sprintf('Candidate profile for user "%s" not found.', $userId));
    }
}
