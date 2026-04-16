<?php

declare(strict_types=1);

namespace App\Domain\Job\Enum;

enum EmploymentType: string
{
    case FULL_TIME = 'full_time';
    case PART_TIME = 'part_time';
    case CONTRACT = 'contract';
    case INTERNSHIP = 'internship';
    case REMOTE = 'remote';
}
