<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\Domain\CandidateProfile\Repository\CandidateProfileRepositoryInterface;
use App\Domain\Job\Repository\JobRepositoryInterface;
use App\Domain\Matching\Repository\MatchRepositoryInterface;
use App\Domain\Matching\Service\MatchingService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:matching:calculate',
    description: 'Calculate matches for a candidate',
)]
final class CalculateMatchesCommand extends Command
{
    public function __construct(
        private CandidateProfileRepositoryInterface $profileRepository,
        private JobRepositoryInterface $jobRepository,
        private MatchRepositoryInterface $matchRepository,
        private MatchingService $matchingService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('user-id', InputArgument::REQUIRED, 'User ID of the candidate');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $userId = $input->getArgument('user-id');

        $profile = $this->profileRepository->getByUserId($userId);
        $jobs = $this->jobRepository->findPublished();

        $io->text(\sprintf('Candidate: %s', $profile->getTitle()));
        $io->text(\sprintf('Published jobs: %d', \count($jobs)));

        $created = 0;
        foreach ($jobs as $job) {
            if (null !== $this->matchRepository->findByCandidateAndJob($profile->getId(), $job->getId())) {
                continue;
            }

            $match = $this->matchingService->calculateScore($profile, $job);

            if ($match->getScore() >= 20) {
                $this->matchRepository->save($match);
                ++$created;
                $io->text(\sprintf('  %s -> %d%% (skills=%.0f%% salary=%.0f%% loc=%.0f%% exp=%.0f%%)',
                    $job->getTitle(),
                    $match->getScore(),
                    $match->getSkillsMatch() * 100,
                    $match->getSalaryMatch() * 100,
                    $match->getLocationMatch() * 100,
                    $match->getExperienceMatch() * 100,
                ));
            }
        }

        $io->success(\sprintf('Created %d matches.', $created));

        return Command::SUCCESS;
    }
}
