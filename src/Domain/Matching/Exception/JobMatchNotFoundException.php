<?php

declare(strict_types=1);

namespace App\Domain\Matching\Exception;

final class JobMatchNotFoundException extends \RuntimeException
{
    public static function byId(string $id): self
    {
        return new self(\sprintf('Match "%s" not found.', $id));
    }
}
