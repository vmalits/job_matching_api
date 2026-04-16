<?php

declare(strict_types=1);

namespace App\Tests\Domain\Matching;

use App\Domain\CandidateProfile\Entity\CandidateProfile;
use App\Domain\Job\Entity\Job;
use App\Domain\Job\Enum\EmploymentType;
use App\Domain\Matching\Service\MatchingService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MatchingServiceTest extends TestCase
{
    private MatchingService $service;

    protected function setUp(): void
    {
        $this->service = new MatchingService();
    }

    #[Test]
    public function perfectMatchScoresHigh(): void
    {
        $profile = $this->createProfile(
            skills: ['PHP', 'Symfony', 'Docker', 'PostgreSQL'],
            location: 'Berlin, Germany',
            experienceYears: 8,
            salaryMin: 80000,
            salaryMax: 120000,
        );
        $job = $this->createJob(
            skills: ['PHP', 'Symfony', 'Docker', 'PostgreSQL'],
            location: 'Berlin, Germany',
            salaryMin: 80000,
            salaryMax: 120000,
        );

        $match = $this->service->calculateScore($profile, $job);

        $this->assertGreaterThanOrEqual(85, $match->getScore());
        $this->assertSame(1.0, $match->getSkillsMatch());
        $this->assertSame(1.0, $match->getLocationMatch());
    }

    #[Test]
    public function partialSkillsMatch(): void
    {
        $profile = $this->createProfile(skills: ['PHP', 'Symfony', 'Docker']);
        $job = $this->createJob(skills: ['PHP', 'Go', 'Kubernetes']);

        $match = $this->service->calculateScore($profile, $job);

        // 1 out of 5 unique skills match = 0.2
        $this->assertEqualsWithDelta(0.2, $match->getSkillsMatch(), 0.01);
    }

    #[Test]
    public function noSkillsMatch(): void
    {
        $profile = $this->createProfile(skills: ['PHP', 'Symfony']);
        $job = $this->createJob(skills: ['Java', 'Spring']);

        $match = $this->service->calculateScore($profile, $job);

        $this->assertSame(0.0, $match->getSkillsMatch());
        $this->assertLessThan(40, $match->getScore());
    }

    #[Test]
    public function salaryExactOverlap(): void
    {
        $match = $this->calculate(
            salaryMin: 80000,
            salaryMax: 120000,
            jobSalaryMin: 90000,
            jobSalaryMax: 110000,
        );

        $this->assertGreaterThanOrEqual(0.3, $match->getSalaryMatch());
    }

    #[Test]
    public function salaryNoOverlap(): void
    {
        $match = $this->calculate(
            salaryMin: 120000,
            salaryMax: 150000,
            jobSalaryMin: 50000,
            jobSalaryMax: 80000,
        );

        // Gap = 40000, avg = 100000, result = max(0, 1 - 40000/100000) = 0.6
        $this->assertLessThan(1.0, $match->getSalaryMatch());
    }

    #[Test]
    public function salaryNullReturnsNeutral(): void
    {
        $profile = $this->createProfile(skills: ['PHP'], salaryMin: null, salaryMax: null);
        $job = $this->createJob(skills: ['PHP'], salaryMin: null, salaryMax: null);

        $match = $this->service->calculateScore($profile, $job);

        $this->assertSame(0.5, $match->getSalaryMatch());
    }

    #[Test]
    public function locationExactMatch(): void
    {
        $match = $this->calculate(
            location: 'Berlin, Germany',
            jobLocation: 'Berlin, Germany',
        );

        $this->assertSame(1.0, $match->getLocationMatch());
    }

    #[Test]
    public function locationSameCityDifferentArea(): void
    {
        $match = $this->calculate(
            location: 'Berlin, Mitte',
            jobLocation: 'Berlin, Kreuzberg',
        );

        $this->assertSame(0.8, $match->getLocationMatch());
    }

    #[Test]
    public function locationSameCountry(): void
    {
        $match = $this->calculate(
            location: 'Berlin, Germany',
            jobLocation: 'Munich, Germany',
        );

        $this->assertSame(0.4, $match->getLocationMatch());
    }

    #[Test]
    public function locationDifferentCountry(): void
    {
        $match = $this->calculate(
            location: 'Berlin, Germany',
            jobLocation: 'London, UK',
        );

        $this->assertSame(0.1, $match->getLocationMatch());
    }

    #[Test]
    public function experienceSeniorScoresHighest(): void
    {
        $profile = $this->createProfile(skills: [], experienceYears: 10);
        $job = $this->createJob(skills: []);

        $match = $this->service->calculateScore($profile, $job);

        $this->assertSame(1.0, $match->getExperienceMatch());
    }

    #[Test]
    public function experienceJuniorScoresLower(): void
    {
        $profile = $this->createProfile(skills: [], experienceYears: 1);
        $job = $this->createJob(skills: []);

        $match = $this->service->calculateScore($profile, $job);

        $this->assertEqualsWithDelta(0.2, $match->getExperienceMatch(), 0.01);
    }

    #[Test]
    public function matchReturnsCorrectIds(): void
    {
        $profile = $this->createProfile(skills: ['PHP']);
        $job = $this->createJob(skills: ['PHP']);

        $match = $this->service->calculateScore($profile, $job);

        $this->assertSame($profile->getId(), $match->getCandidateProfileId());
        $this->assertSame($job->getId(), $match->getJobId());
    }

    #[Test]
    public function scoreIsBetweenZeroAndHundred(): void
    {
        $profile = $this->createProfile(
            skills: ['PHP', 'Symfony', 'Docker'],
            location: 'Berlin, Germany',
            experienceYears: 5,
            salaryMin: 80000,
            salaryMax: 120000,
        );
        $job = $this->createJob(
            skills: ['Java', 'Spring'],
            location: 'Tokyo, Japan',
            salaryMin: 30000,
            salaryMax: 50000,
        );

        $match = $this->service->calculateScore($profile, $job);

        $this->assertGreaterThanOrEqual(0, $match->getScore());
        $this->assertLessThanOrEqual(100, $match->getScore());
    }

    #[Test]
    public function emptyJobSkillsReturnsNeutralSkillsScore(): void
    {
        $profile = $this->createProfile(skills: ['PHP', 'Symfony']);
        $job = $this->createJob(skills: []);

        $match = $this->service->calculateScore($profile, $job);

        $this->assertSame(0.5, $match->getSkillsMatch());
    }

    // Helper methods

    private function calculate(
        array $skills = ['PHP'],
        array $jobSkills = ['PHP'],
        string $location = 'Berlin, Germany',
        string $jobLocation = 'Berlin, Germany',
        int $experienceYears = 5,
        ?int $salaryMin = 80000,
        ?int $salaryMax = 120000,
        ?int $jobSalaryMin = 80000,
        ?int $jobSalaryMax = 120000,
    ) {
        $profile = $this->createProfile(
            skills: $skills,
            location: $location,
            experienceYears: $experienceYears,
            salaryMin: $salaryMin,
            salaryMax: $salaryMax,
        );
        $job = $this->createJob(
            skills: $jobSkills,
            location: $jobLocation,
            salaryMin: $jobSalaryMin,
            salaryMax: $jobSalaryMax,
        );

        return $this->service->calculateScore($profile, $job);
    }

    private function createProfile(
        array $skills = [],
        string $location = 'Berlin, Germany',
        int $experienceYears = 5,
        ?int $salaryMin = null,
        ?int $salaryMax = null,
    ): CandidateProfile {
        $profile = new CandidateProfile(
            userId: uniqid('', true),
            title: 'Developer',
            bio: 'Bio',
            location: $location,
            experienceYears: $experienceYears,
            skills: $skills,
        );
        $profile->setSalaryMin($salaryMin);
        $profile->setSalaryMax($salaryMax);

        return $profile;
    }

    private function createJob(
        array $skills = [],
        string $location = 'Berlin, Germany',
        ?int $salaryMin = null,
        ?int $salaryMax = null,
    ): Job {
        $job = new Job(
            recruiterId: 'recruiter-1',
            title: 'Developer',
            description: 'Desc',
            companyName: 'Corp',
            location: $location,
            employmentType: EmploymentType::FULL_TIME,
            skills: $skills,
        );
        $job->setSalaryMin($salaryMin);
        $job->setSalaryMax($salaryMax);

        return $job;
    }
}
