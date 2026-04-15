<?php

declare(strict_types=1);

namespace App\Domain\User\Enum;

enum Role: string
{
    case CANDIDATE = 'ROLE_CANDIDATE';
    case RECRUITER = 'ROLE_RECRUITER';

    /**
     * @return list<self>
     */
    public static function values(): array
    {
        return self::cases();
    }

    public function label(): string
    {
        return match ($this) {
            self::CANDIDATE => 'Candidate',
            self::RECRUITER => 'Recruiter',
        };
    }
}
