<?php

declare(strict_types=1);

namespace App\Domain\Job\Exception;

final class JobNotFoundException extends \RuntimeException
{
    public static function byId(string $id): self
    {
        return new self(\sprintf('Job "%s" not found.', $id));
    }
}
