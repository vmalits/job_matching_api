<?php

declare(strict_types=1);

namespace App\Domain\Job\Enum;

enum JobStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case CLOSED = 'closed';
    case ARCHIVED = 'archived';
}
