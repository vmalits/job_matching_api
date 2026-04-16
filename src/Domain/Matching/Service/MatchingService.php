<?php

declare(strict_types=1);

namespace App\Domain\Matching\Service;

use App\Domain\CandidateProfile\Entity\CandidateProfile;
use App\Domain\Job\Entity\Job;
use App\Domain\Matching\Entity\JobMatch;

final readonly class MatchingService
{
    /**
     * Weights for each factor in the final score (must sum to 1.0).
     */
    private const float WEIGHT_SKILLS = 0.50;
    private const float WEIGHT_SALARY = 0.20;
    private const float WEIGHT_LOCATION = 0.15;
    private const float WEIGHT_EXPERIENCE = 0.15;

    /**
     * Calculate a match score between a candidate profile and a job.
     * Returns 0-100.
     */
    public function calculateScore(CandidateProfile $profile, Job $job): JobMatch
    {
        $skillsScore = $this->calculateSkillsMatch($profile->getSkills(), $job->getSkills());
        $salaryScore = $this->calculateSalaryMatch($profile, $job);
        $locationScore = $this->calculateLocationMatch($profile->getLocation(), $job->getLocation());
        $experienceScore = $this->calculateExperienceMatch($profile->getExperienceYears());

        $total = ($skillsScore * self::WEIGHT_SKILLS
                + $salaryScore * self::WEIGHT_SALARY
                + $locationScore * self::WEIGHT_LOCATION
                + $experienceScore * self::WEIGHT_EXPERIENCE) * 100;

        return new JobMatch(
            candidateProfileId: $profile->getId(),
            jobId: $job->getId(),
            score: (int) round($total),
            skillsMatch: round($skillsScore, 2),
            salaryMatch: round($salaryScore, 2),
            locationMatch: round($locationScore, 2),
            experienceMatch: round($experienceScore, 2),
        );
    }

    /**
     * Jaccard similarity: |intersection| / |union|.
     *
     * @param list<string> $candidateSkills
     * @param list<string> $jobSkills
     */
    private function calculateSkillsMatch(array $candidateSkills, array $jobSkills): float
    {
        if (0 === \count($jobSkills)) {
            return 0.5;
        }

        $candidateSet = array_map(strtolower(...), $candidateSkills);
        $jobSet = array_map(strtolower(...), $jobSkills);

        $intersection = array_intersect($candidateSet, $jobSet);
        $union = array_unique(array_merge($candidateSet, $jobSet));

        return \count($intersection) / \count($union);
    }

    private function calculateSalaryMatch(CandidateProfile $profile, Job $job): float
    {
        $cMin = $profile->getSalaryMin();
        $cMax = $profile->getSalaryMax();
        $jMin = $job->getSalaryMin();
        $jMax = $job->getSalaryMax();

        if (null === $cMin && null === $cMax && null === $jMin && null === $jMax) {
            return 0.5;
        }

        if (null === $cMin || null === $jMin) {
            return 0.5;
        }

        $cMax ??= $cMin;
        $jMax ??= $jMin;

        // Full overlap
        if ($cMin <= $jMax && $cMax >= $jMin) {
            $overlapMin = max($cMin, $jMin);
            $overlapMax = min($cMax, $jMax);
            $range = max($cMax - $cMin, $jMax - $jMin, 1);

            return max(0.3, $overlapMax - $overlapMin) / $range;
        }

        // No overlap — distance penalty
        $gap = $cMin > $jMax ? $cMin - $jMax : $jMin - $cMax;
        $avgSalary = (($cMin + $cMax + $jMin + $jMax) / 4);

        return max(0, 1 - ($gap / max($avgSalary, 1)));
    }

    private function calculateLocationMatch(string $candidateLocation, string $jobLocation): float
    {
        if (strtolower($candidateLocation) === strtolower($jobLocation)) {
            return 1.0;
        }

        // Same city (partial match after comma)
        $cParts = array_map('trim', explode(',', strtolower($candidateLocation)));
        $jParts = array_map('trim', explode(',', strtolower($jobLocation)));

        if ($cParts[0] === $jParts[0]) {
            return 0.8;
        }

        // Same country
        if (($cParts[1] ?? '') === ($jParts[1] ?? '')) {
            return 0.4;
        }

        return 0.1;
    }

    private function calculateExperienceMatch(int $experienceYears): float
    {
        // More experience = higher score, capped at 1.0
        // Junior (0-2) = 0.3, Mid (3-5) = 0.6, Senior (6+) = 1.0
        return match (true) {
            $experienceYears >= 6 => 1.0,
            $experienceYears >= 3 => 0.6 + (($experienceYears - 3) * 0.13),
            default => 0.1 + ($experienceYears * 0.1),
        };
    }
}
