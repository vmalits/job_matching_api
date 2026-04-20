<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Domain\Job\Entity\Job;
use App\Domain\Job\Repository\JobRepositoryInterface;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Infrastructure\ApiPlatform\Resource\JobResource;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements ProviderInterface<JobResource>
 */
final readonly class JobProvider implements ProviderInterface
{
    public function __construct(
        private JobRepositoryInterface $jobRepository,
        private UserRepositoryInterface $userRepository,
        private Security $security,
    ) {
    }

    /**
     * @return JobResource|list<JobResource>|null
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): JobResource|array|null
    {
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

        if (isset($uriVariables['id'])) {
            $job = $this->jobRepository->findById($uriVariables['id']);
            if (null === $job) {
                return null;
            }

            if (!$job->isPublished() && $job->getRecruiterId() !== $currentUser->getId()) {
                return null;
            }

            return $this->toResource($job, $currentUser);
        }

        if ($operation instanceof GetCollection) {
            if ($currentUser->isCandidate()) {
                $jobs = $this->jobRepository->findPublished();

                return array_map(fn (Job $j): JobResource => $this->toResource($j, $currentUser), $jobs);
            }

            if ($currentUser->isRecruiter()) {
                $jobs = $this->jobRepository->findByRecruiterId($currentUser->getId());

                return array_map(fn (Job $j): JobResource => $this->toResource($j, $currentUser), $jobs);
            }

            return [];
        }

        return null;
    }

    private function toResource(Job $job, ?User $viewer = null): JobResource
    {
        $recruiter = $this->userRepository->getById($job->getRecruiterId());

        $showSalary = $job->isSalaryVisible()
            || (null !== $viewer && $viewer->getId() === $job->getRecruiterId());

        $resource = new JobResource();
        $resource->id = $job->getId();
        $resource->recruiterId = $job->getRecruiterId();
        $resource->title = $job->getTitle();
        $resource->description = $job->getDescription();
        $resource->companyName = $job->getCompanyName();
        $resource->location = $job->getLocation();
        $resource->employmentType = $job->getEmploymentType()->value;
        $resource->status = $job->getStatus()->value;
        $resource->skills = $job->getSkills();
        $resource->salaryMin = $showSalary ? $job->getSalaryMin() : null;
        $resource->salaryMax = $showSalary ? $job->getSalaryMax() : null;
        $resource->salaryVisible = $job->isSalaryVisible();
        $resource->createdAt = $job->getCreatedAt();
        $resource->recruiterEmail = $recruiter->getEmail();

        return $resource;
    }
}
