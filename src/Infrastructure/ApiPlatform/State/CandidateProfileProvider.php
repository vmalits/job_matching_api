<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Domain\CandidateProfile\Entity\CandidateProfile;
use App\Domain\CandidateProfile\Repository\CandidateProfileRepositoryInterface;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Infrastructure\ApiPlatform\Resource\CandidateProfileResource;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements ProviderInterface<CandidateProfileResource>
 */
final readonly class CandidateProfileProvider implements ProviderInterface
{
    public function __construct(
        private CandidateProfileRepositoryInterface $profileRepository,
        private UserRepositoryInterface $userRepository,
        private Security $security,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?CandidateProfileResource
    {
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

        if (isset($uriVariables['id'])) {
            $profile = $this->profileRepository->findById($uriVariables['id']);

            if (null === $profile) {
                return null;
            }

            if ($currentUser->isCandidate() && $profile->getUserId() !== $currentUser->getId()) {
                return null;
            }

            return $this->toResource($profile);
        }

        $profile = $this->profileRepository->getByUserId($currentUser->getId());

        return $this->toResource($profile);
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
