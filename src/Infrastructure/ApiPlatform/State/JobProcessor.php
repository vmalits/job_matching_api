<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Domain\Job\Entity\Job;
use App\Domain\Job\Enum\EmploymentType;
use App\Domain\Job\Enum\JobStatus;
use App\Domain\Job\Repository\JobRepositoryInterface;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Infrastructure\ApiPlatform\Resource\JobResource;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @implements ProcessorInterface<JobResource, JobResource>
 */
final readonly class JobProcessor implements ProcessorInterface
{
    public function __construct(
        private JobRepositoryInterface $jobRepository,
        private UserRepositoryInterface $userRepository,
        private Security $security,
    ) {
    }

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): JobResource {
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

        if (isset($uriVariables['id'])) {
            $job = $this->jobRepository->getById($uriVariables['id']);

            if ($job->getRecruiterId() !== $currentUser->getId()) {
                throw new AccessDeniedHttpException('You can only edit your own jobs.');
            }

            $this->updateJob($job, $data);
            $this->jobRepository->save($job);

            return $this->toResource($job, $currentUser);
        }

        /** @var non-empty-string $title */
        $title = $data->title;
        /** @var non-empty-string $description */
        $description = $data->description;
        /** @var non-empty-string $companyName */
        $companyName = $data->companyName;
        /** @var non-empty-string $location */
        $location = $data->location;
        /** @var non-empty-string $employmentTypeValue */
        $employmentTypeValue = $data->employmentType;

        $job = new Job(
            recruiterId: $currentUser->getId(),
            title: $title,
            description: $description,
            companyName: $companyName,
            location: $location,
            employmentType: EmploymentType::from($employmentTypeValue),
            skills: $data->skills,
        );
        $job->setSalaryMin($data->salaryMin);
        $job->setSalaryMax($data->salaryMax);
        $job->setSalaryVisible($data->salaryVisible);

        if (JobStatus::PUBLISHED->value === $data->status) {
            $job->publish();
        }

        $this->jobRepository->save($job);

        return $this->toResource($job, $currentUser);
    }

    private function updateJob(Job $job, JobResource $data): void
    {
        if (null !== $data->title) {
            $job->setTitle($data->title);
        }
        if (null !== $data->description) {
            $job->setDescription($data->description);
        }
        if (null !== $data->companyName) {
            $job->setCompanyName($data->companyName);
        }
        if (null !== $data->location) {
            $job->setLocation($data->location);
        }
        if (null !== $data->employmentType) {
            $job->setEmploymentType(EmploymentType::from($data->employmentType));
        }
        $job->setSkills($data->skills);
        $job->setSalaryMin($data->salaryMin);
        $job->setSalaryMax($data->salaryMax);
        $job->setSalaryVisible($data->salaryVisible);

        if (JobStatus::PUBLISHED->value === $data->status && !$job->isPublished()) {
            $job->publish();
        }
        if (JobStatus::CLOSED->value === $data->status) {
            $job->close();
        }
    }

    private function toResource(Job $job, User $currentUser): JobResource
    {
        $recruiter = $this->userRepository->getById($job->getRecruiterId());

        $showSalary = $job->isSalaryVisible()
            || $currentUser->getId() === $job->getRecruiterId();

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
