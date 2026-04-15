<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Domain\CandidateProfile\Entity\CandidateProfile;
use App\Domain\CandidateProfile\Repository\CandidateProfileRepositoryInterface;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Infrastructure\ApiPlatform\Resource\CandidateProfileResource;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements ProcessorInterface<CandidateProfileResource, CandidateProfileResource>
 */
final readonly class CandidateProfileProcessor implements ProcessorInterface
{
    public function __construct(
        private CandidateProfileRepositoryInterface $profileRepository,
        private UserRepositoryInterface $userRepository,
        private Security $security,
    ) {
    }

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): CandidateProfileResource {
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

        // Update existing profile
        $existing = $this->profileRepository->existsByUserId($currentUser->getId())
            ? $this->profileRepository->getByUserId($currentUser->getId())
            : null;

        if (null !== $existing) {
            $this->updateProfile($existing, $data);
            $this->profileRepository->save($existing);

            return $this->toResource($existing);
        }

        // Create new profile
        /** @var non-empty-string $title */
        $title = $data->title;
        /** @var non-empty-string $bio */
        $bio = $data->bio;
        /** @var non-empty-string $location */
        $location = $data->location;

        $profile = new CandidateProfile(
            userId: $currentUser->getId(),
            title: $title,
            bio: $bio,
            location: $location,
            experienceYears: $data->experienceYears ?? 0,
            skills: $data->skills,
        );
        $profile->setSalaryMin($data->salaryMin);
        $profile->setSalaryMax($data->salaryMax);
        $profile->setResumeUrl($data->resumeUrl);

        $this->profileRepository->save($profile);

        return $this->toResource($profile);
    }

    private function updateProfile(CandidateProfile $profile, CandidateProfileResource $data): void
    {
        if (null !== $data->title) {
            $profile->setTitle($data->title);
        }
        if (null !== $data->bio) {
            $profile->setBio($data->bio);
        }
        if (null !== $data->location) {
            $profile->setLocation($data->location);
        }
        if (null !== $data->experienceYears) {
            $profile->setExperienceYears($data->experienceYears);
        }
        $profile->setSkills($data->skills);
        $profile->setSalaryMin($data->salaryMin);
        $profile->setSalaryMax($data->salaryMax);
        $profile->setResumeUrl($data->resumeUrl);
    }

    private function toResource(CandidateProfile $profile): CandidateProfileResource
    {
        $user = $this->userRepository->getById($profile->getUserId());

        $resource = new CandidateProfileResource();
        $resource->id = $profile->getId();
        $resource->userId = $profile->getUserId();
        $resource->title = $profile->getTitle();
        $resource->bio = $profile->getBio();
        $resource->location = $profile->getLocation();
        $resource->experienceYears = $profile->getExperienceYears();
        $resource->skills = $profile->getSkills();
        $resource->salaryMin = $profile->getSalaryMin();
        $resource->salaryMax = $profile->getSalaryMax();
        $resource->resumeUrl = $profile->getResumeUrl();
        $resource->createdAt = $profile->getCreatedAt();
        $resource->userEmail = $user->getEmail();

        return $resource;
    }
}
