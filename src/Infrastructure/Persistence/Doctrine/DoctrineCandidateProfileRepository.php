<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\CandidateProfile\Entity\CandidateProfile;
use App\Domain\CandidateProfile\Exception\CandidateProfileNotFoundException;
use App\Domain\CandidateProfile\Repository\CandidateProfileRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineCandidateProfileRepository implements CandidateProfileRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function save(CandidateProfile $profile): void
    {
        $this->em->persist($profile);
        $this->em->flush();
    }

    public function remove(CandidateProfile $profile): void
    {
        $this->em->remove($profile);
        $this->em->flush();
    }

    public function getByUserId(string $userId): CandidateProfile
    {
        $profile = $this->em
            ->getRepository(CandidateProfile::class)
            ->findOneBy(['userId' => $userId]);

        if (null === $profile) {
            throw CandidateProfileNotFoundException::byUserId($userId);
        }

        return $profile;
    }

    public function findById(string $id): ?CandidateProfile
    {
        return $this->em->find(CandidateProfile::class, $id);
    }

    public function existsByUserId(string $userId): bool
    {
        return null !== $this->em
            ->getRepository(CandidateProfile::class)
            ->findOneBy(['userId' => $userId]);
    }
}
