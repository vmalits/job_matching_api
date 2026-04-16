<?php

declare(strict_types=1);

namespace App\Application\Matching\Message;

final readonly class CalculateMatchesMessage
{
    public function __construct(
        private string $candidateProfileId,
    ) {
    }

    public function getCandidateProfileId(): string
    {
        return $this->candidateProfileId;
    }
}
