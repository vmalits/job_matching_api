<?php

declare(strict_types=1);

namespace App\Domain\Matching\Enum;

enum MatchStatus: string
{
    case PENDING = 'pending';
    case VIEWED = 'viewed';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
}
