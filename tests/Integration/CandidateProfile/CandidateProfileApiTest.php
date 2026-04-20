<?php

declare(strict_types=1);

namespace App\Tests\Integration\CandidateProfile;

use App\Tests\Integration\ApiTestCase;
use PHPUnit\Framework\Attributes\Test;

class CandidateProfileApiTest extends ApiTestCase
{
    #[Test]
    public function candidateCanCreateProfile(): void
    {
        $user = $this->registerCandidate();
        $this->authenticate($user['token']);
        $this->client->request('POST', '/api/candidate_profiles', [
            'json' => [
                'title' => 'PHP Developer',
                'bio' => 'Experienced developer',
                'location' => 'Berlin, Germany',
                'experienceYears' => 5,
                'skills' => ['PHP', 'Symfony'],
                'salaryMin' => 80000,
                'salaryMax' => 120000,
            ],
        ]);

        static::assertResponseIsSuccessful();
        static::assertJsonContains([
            'title' => 'PHP Developer',
            'bio' => 'Experienced developer',
            'location' => 'Berlin, Germany',
            'experienceYears' => 5,
            'skills' => ['PHP', 'Symfony'],
            'salaryMin' => 80000,
            'salaryMax' => 120000,
            'userId' => $user['id'],
            'userEmail' => $user['email'],
        ]);

        $data = $this->client->getResponse()->toArray();
        static::assertArrayHasKey('id', $data);
        static::assertArrayHasKey('createdAt', $data);
    }

    #[Test]
    public function candidateCanUpdateProfile(): void
    {
        $result = $this->createCandidateWithProfile();
        $this->authenticate($result['user']['token']);

        $this->client->request('PATCH', '/api/candidate_profiles/'.$result['profile']['id'], [
            'json' => ['title' => 'Senior PHP Developer'],
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);

        static::assertResponseIsSuccessful();
        static::assertJsonContains(['title' => 'Senior PHP Developer']);
    }

    #[Test]
    public function candidateCanGetOwnProfile(): void
    {
        $result = $this->createCandidateWithProfile();
        $this->authenticate($result['user']['token']);
        $this->client->request('GET', '/api/candidate_profiles/'.$result['profile']['id']);

        static::assertResponseIsSuccessful();
        static::assertJsonContains([
            'id' => $result['profile']['id'],
            'userId' => $result['user']['id'],
        ]);
    }

    #[Test]
    public function recruiterCannotCreateProfile(): void
    {
        $user = $this->registerRecruiter();
        $this->authenticate($user['token']);
        $this->client->request('POST', '/api/candidate_profiles', [
            'json' => [
                'title' => 'PHP Developer',
                'bio' => 'Test',
                'location' => 'Berlin',
                'experienceYears' => 3,
                'skills' => ['PHP'],
            ],
        ]);

        static::assertResponseStatusCodeSame(403);
    }

    #[Test]
    public function candidateCannotViewOtherCandidateProfile(): void
    {
        $resultA = $this->createCandidateWithProfile();
        $userB = $this->registerCandidate('other-candidate@test.com');
        $this->authenticate($userB['token']);

        $this->client->request('GET', '/api/candidate_profiles/'.$resultA['profile']['id']);

        static::assertResponseStatusCodeSame(404);
    }

    #[Test]
    public function unauthenticatedAccessReturns401(): void
    {
        $this->client->request('POST', '/api/candidate_profiles', [
            'json' => [
                'title' => 'PHP Developer',
                'bio' => 'Test',
                'location' => 'Berlin',
                'experienceYears' => 3,
            ],
        ]);

        static::assertResponseStatusCodeSame(401);
    }

    #[Test]
    public function profileValidationRequiresRequiredFields(): void
    {
        $user = $this->registerCandidate();
        $this->authenticate($user['token']);
        $this->client->request('POST', '/api/candidate_profiles', [
            'json' => [
                'bio' => 'Test',
                'location' => 'Berlin',
            ],
        ]);

        static::assertResponseStatusCodeSame(422);
        $data = $this->client->getResponse()->toArray(false);
        $detail = $data['detail'] ?? $data['hydra:description'] ?? '';
        static::assertStringContainsString('title', $detail);
        static::assertStringContainsString('experienceYears', $detail);
    }
}
