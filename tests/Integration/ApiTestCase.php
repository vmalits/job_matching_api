<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase as BaseApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Domain\Matching\Entity\JobMatch;
use App\Domain\Matching\Repository\MatchRepositoryInterface;

abstract class ApiTestCase extends BaseApiTestCase
{
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();

        $em = static::getContainer()->get('doctrine')->getManager();
        $conn = $em->getConnection();
        $conn->executeStatement('TRUNCATE TABLE matches, jobs, candidate_profiles, users RESTART IDENTITY CASCADE');
    }

    protected function registerUser(string $email, string $role, string $password = 'password123'): array
    {
        $this->client->request('POST', '/api/auth/register', [
            'json' => [
                'email' => $email,
                'password' => $password,
                'firstName' => 'Test',
                'lastName' => 'User',
                'role' => $role,
            ],
        ]);

        $data = $this->client->getResponse()->toArray();

        return [
            'id' => $data['id'],
            'token' => $data['token'],
            'email' => $data['email'],
        ];
    }

    protected function registerCandidate(?string $email = null): array
    {
        return $this->registerUser($email ?? 'candidate-'.uniqid().'@test.com', 'ROLE_CANDIDATE');
    }

    protected function registerRecruiter(?string $email = null): array
    {
        return $this->registerUser($email ?? 'recruiter-'.uniqid().'@test.com', 'ROLE_RECRUITER');
    }

    protected function authenticate(string $token): void
    {
        $this->client->setDefaultOptions([
            'headers' => [
                'Authorization' => 'Bearer '.$token,
                'Accept' => 'application/ld+json',
            ],
        ]);
    }

    protected function createCandidateWithProfile(): array
    {
        $user = $this->registerCandidate();
        $this->authenticate($user['token']);

        $this->client->request('POST', '/api/candidate_profiles', ['json' => [
            'title' => 'PHP Developer',
            'bio' => 'Experienced developer',
            'location' => 'Berlin, Germany',
            'experienceYears' => 5,
            'skills' => ['PHP', 'Symfony'],
            'salaryMin' => 80000,
            'salaryMax' => 120000,
        ]]);
        $profile = $this->client->getResponse()->toArray();

        return [
            'user' => $user,
            'profile' => $profile,
        ];
    }

    protected function createRecruiterWithPublishedJob(): array
    {
        $user = $this->registerRecruiter();
        $this->authenticate($user['token']);

        $this->client->request('POST', '/api/jobs', ['json' => [
            'title' => 'Senior PHP Developer',
            'description' => 'We need a developer',
            'companyName' => 'Acme Corp',
            'location' => 'Berlin, Germany',
            'employmentType' => 'full_time',
            'skills' => ['PHP', 'Symfony'],
            'salaryMin' => 80000,
            'salaryMax' => 120000,
            'salaryVisible' => true,
            'status' => 'published',
        ]]);
        $job = $this->client->getResponse()->toArray();

        return [
            'user' => $user,
            'job' => $job,
        ];
    }

    protected function createTestMatch(string $candidateProfileId, string $jobId, int $score = 85): JobMatch
    {
        $match = new JobMatch(
            candidateProfileId: $candidateProfileId,
            jobId: $jobId,
            score: $score,
            skillsMatch: 80.0,
            salaryMatch: 90.0,
            locationMatch: 100.0,
            experienceMatch: 70.0,
        );

        /** @var MatchRepositoryInterface $repo */
        $repo = static::getContainer()->get(MatchRepositoryInterface::class);
        $repo->save($match);

        $em = static::getContainer()->get('doctrine')->getManager();
        $em->clear();

        return $match;
    }
}
