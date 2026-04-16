<?php

declare(strict_types=1);

namespace App\Application\Matching\Handler;

use App\Application\Matching\Message\CalculateMatchesMessage;
use App\Domain\CandidateProfile\Repository\CandidateProfileRepositoryInterface;
use App\Domain\Job\Repository\JobRepositoryInterface;
use App\Domain\Matching\Repository\MatchRepositoryInterface;
use App\Domain\Matching\Service\MatchingService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CalculateMatchesHandler
{
    public function __construct(
        private CandidateProfileRepositoryInterface $profileRepository,
        private JobRepositoryInterface $jobRepository,
        private MatchRepositoryInterface $matchRepository,
        private MatchingService $matchingService,
    ) {
    }

    public function __invoke(CalculateMatchesMessage $message): void
    {
        $profile = $this->profileRepository->getByUserId($message->getCandidateProfileId());
        $publishedJobs = $this->jobRepository->findPublished();

        foreach ($publishedJobs as $job) {
            $existing = $this->matchRepository->findByCandidateAndJob($profile->getId(), $job->getId());

            if (null !== $existing) {
                continue;
            }

            $match = $this->matchingService->calculateScore($profile, $job);

            if ($match->getScore() >= 20) {
                $this->matchRepository->save($match);
            }
        }
    }
}
