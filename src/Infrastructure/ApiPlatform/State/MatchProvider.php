<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Domain\CandidateProfile\Repository\CandidateProfileRepositoryInterface;
use App\Domain\Job\Repository\JobRepositoryInterface;
use App\Domain\Matching\Entity\JobMatch;
use App\Domain\Matching\Repository\MatchRepositoryInterface;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Infrastructure\ApiPlatform\Resource\MatchResource;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements ProviderInterface<MatchResource>
 */
final readonly class MatchProvider implements ProviderInterface
{
    public function __construct(
        private MatchRepositoryInterface $matchRepository,
        private CandidateProfileRepositoryInterface $profileRepository,
        private JobRepositoryInterface $jobRepository,
        private UserRepositoryInterface $userRepository,
        private Security $security,
    ) {
    }

    /**
     * @return MatchResource|list<MatchResource>|null
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): MatchResource|array|null
    {
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

        if (isset($uriVariables['id'])) {
            $match = $this->matchRepository->getById($uriVariables['id']);
            $this->markViewed($match, $currentUser);

            return $this->toResource($match, $currentUser);
        }

        if ($operation instanceof GetCollection) {
            if ($currentUser->isCandidate()) {
                $profile = $this->profileRepository->getByUserId($currentUser->getId());
                $matches = $this->matchRepository->findTopForCandidate($profile->getId());
            } else {
                // Recruiter: pass ?jobId=xxx to filter
                $jobId = $context['filters']['jobId'] ?? null;
                if (\is_string($jobId) && '' !== $jobId) {
                    $matches = $this->matchRepository->findTopForJob($jobId);
                } else {
                    $matches = [];
                }
            }

            return array_map(fn (JobMatch $m): MatchResource => $this->toResource($m, $currentUser), $matches);
        }

        return null;
    }

    private function markViewed(JobMatch $match, User $currentUser): void
    {
        if ($currentUser->isCandidate()) {
            $profile = $this->profileRepository->getByUserId($currentUser->getId());
            if ($profile->getId() === $match->getCandidateProfileId()) {
                $match->markViewed();
                $this->matchRepository->save($match);
            }
        }
    }

    private function toResource(JobMatch $match, User $currentUser): MatchResource
    {
        $resource = new MatchResource();
        $resource->id = $match->getId();
        $resource->candidateProfileId = $match->getCandidateProfileId();
        $resource->jobId = $match->getJobId();
        $resource->score = $match->getScore();
        $resource->status = $match->getStatus()->value;
        $resource->skillsMatch = $match->getSkillsMatch();
        $resource->salaryMatch = $match->getSalaryMatch();
        $resource->locationMatch = $match->getLocationMatch();
        $resource->experienceMatch = $match->getExperienceMatch();
        $resource->createdAt = $match->getCreatedAt();

        // Enrich with job data
        $job = $this->jobRepository->findById($match->getJobId());
        if (null !== $job) {
            $resource->jobTitle = $job->getTitle();
            $resource->jobCompanyName = $job->getCompanyName();
            $resource->jobLocation = $job->getLocation();
            $resource->jobEmploymentType = $job->getEmploymentType()->value;
        }

        // Enrich with candidate email for recruiters
        if ($currentUser->isRecruiter()) {
            $profile = $this->profileRepository->findById($match->getCandidateProfileId());
            if (null !== $profile) {
                $candidate = $this->userRepository->getById($profile->getUserId());
                $resource->candidateEmail = $candidate->getEmail();
            }
        }

        return $resource;
    }
}
