<?php

declare(strict_types=1);

namespace App\Tests\Domain\CandidateProfile;

use App\Domain\CandidateProfile\Entity\CandidateProfile;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CandidateProfileTest extends TestCase
{
    #[Test]
    public function itCreatesProfileWithRequiredFields(): void
    {
        $profile = $this->createProfile();

        $this->assertNotEmpty($profile->getId());
        $this->assertSame('Senior Developer', $profile->getTitle());
        $this->assertSame('Experienced developer', $profile->getBio());
        $this->assertSame('Berlin, Germany', $profile->getLocation());
        $this->assertSame(5, $profile->getExperienceYears());
        $this->assertSame(['PHP', 'Symfony'], $profile->getSkills());
    }

    #[Test]
    public function itSetsSalary(): void
    {
        $profile = $this->createProfile();
        $profile->setSalaryMin(80000);
        $profile->setSalaryMax(120000);

        $this->assertSame(80000, $profile->getSalaryMin());
        $this->assertSame(120000, $profile->getSalaryMax());
    }

    #[Test]
    public function itUpdatesTitle(): void
    {
        $profile = $this->createProfile();
        $profile->setTitle('Lead Developer');

        $this->assertSame('Lead Developer', $profile->getTitle());
    }

    #[Test]
    public function itUpdatesSkills(): void
    {
        $profile = $this->createProfile();
        $profile->setSkills(['PHP', 'Go', 'Kubernetes']);

        $this->assertSame(['PHP', 'Go', 'Kubernetes'], $profile->getSkills());
    }

    #[Test]
    public function itSetsResumeUrl(): void
    {
        $profile = $this->createProfile();
        $profile->setResumeUrl('https://example.com/resume.pdf');

        $this->assertSame('https://example.com/resume.pdf', $profile->getResumeUrl());
    }

    #[Test]
    public function itHasTimestamps(): void
    {
        $profile = $this->createProfile();

        $this->assertInstanceOf(\DateTimeImmutable::class, $profile->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $profile->getUpdatedAt());
    }

    private function createProfile(): CandidateProfile
    {
        return new CandidateProfile(
            userId: 'user-123',
            title: 'Senior Developer',
            bio: 'Experienced developer',
            location: 'Berlin, Germany',
            experienceYears: 5,
            skills: ['PHP', 'Symfony'],
        );
    }
}
