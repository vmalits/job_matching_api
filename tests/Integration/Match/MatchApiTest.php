<?php

declare(strict_types=1);

namespace App\Tests\Integration\Match;

use App\Tests\Integration\ApiTestCase;
use PHPUnit\Framework\Attributes\Test;

class MatchApiTest extends ApiTestCase
{
    #[Test]
    public function candidateCanSeeOwnMatches(): void
    {
        $candidateResult = $this->createCandidateWithProfile();
        $recruiterResult = $this->createRecruiterWithPublishedJob();

        $this->createTestMatch(
            $candidateResult['profile']['id'],
            $recruiterResult['job']['id'],
        );

        $this->authenticate($candidateResult['user']['token']);
        $this->client->request('GET', '/api/matches');

        static::assertResponseIsSuccessful();
        $data = $this->client->getResponse()->toArray();
        static::assertArrayHasKey('member', $data);
        static::assertCount(1, $data['member']);
    }

    #[Test]
    public function candidateMatchContainsJobDetails(): void
    {
        $candidateResult = $this->createCandidateWithProfile();
        $recruiterResult = $this->createRecruiterWithPublishedJob();

        $match = $this->createTestMatch(
            $candidateResult['profile']['id'],
            $recruiterResult['job']['id'],
        );

        $this->authenticate($candidateResult['user']['token']);
        $this->client->request('GET', '/api/matches/'.$match->getId());

        static::assertResponseIsSuccessful();
        static::assertJsonContains([
            'id' => $match->getId(),
            'score' => 85,
            'jobTitle' => 'Senior PHP Developer',
            'jobCompanyName' => 'Acme Corp',
            'jobLocation' => 'Berlin, Germany',
            'jobEmploymentType' => 'full_time',
        ]);

        $data = $this->client->getResponse()->toArray();
        static::assertNull($data['candidateEmail'] ?? null);
    }

    #[Test]
    public function recruiterCanSeeMatchesForJob(): void
    {
        $candidateResult = $this->createCandidateWithProfile();
        $recruiterResult = $this->createRecruiterWithPublishedJob();

        $this->createTestMatch(
            $candidateResult['profile']['id'],
            $recruiterResult['job']['id'],
        );

        $this->authenticate($recruiterResult['user']['token']);
        $this->client->request('GET', '/api/matches?jobId='.$recruiterResult['job']['id']);

        static::assertResponseIsSuccessful();
        $data = $this->client->getResponse()->toArray();
        static::assertCount(1, $data['member']);
        static::assertSame($candidateResult['user']['email'], $data['member'][0]['candidateEmail']);
    }

    #[Test]
    public function candidateCanAcceptMatch(): void
    {
        $candidateResult = $this->createCandidateWithProfile();
        $recruiterResult = $this->createRecruiterWithPublishedJob();

        $match = $this->createTestMatch(
            $candidateResult['profile']['id'],
            $recruiterResult['job']['id'],
        );

        $this->authenticate($candidateResult['user']['token']);
        $this->client->request('PATCH', '/api/matches/'.$match->getId(), [
            'json' => ['action' => 'accept'],
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);

        static::assertResponseIsSuccessful();
        static::assertJsonContains(['status' => 'accepted']);
    }

    #[Test]
    public function candidateCanRejectMatch(): void
    {
        $candidateResult = $this->createCandidateWithProfile();
        $recruiterResult = $this->createRecruiterWithPublishedJob();

        $match = $this->createTestMatch(
            $candidateResult['profile']['id'],
            $recruiterResult['job']['id'],
        );

        $this->authenticate($candidateResult['user']['token']);
        $this->client->request('PATCH', '/api/matches/'.$match->getId(), [
            'json' => ['action' => 'reject'],
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);

        static::assertResponseIsSuccessful();
        static::assertJsonContains(['status' => 'rejected']);
    }

    #[Test]
    public function viewingMatchAsCandidateMarksItViewed(): void
    {
        $candidateResult = $this->createCandidateWithProfile();
        $recruiterResult = $this->createRecruiterWithPublishedJob();

        $match = $this->createTestMatch(
            $candidateResult['profile']['id'],
            $recruiterResult['job']['id'],
        );

        $this->authenticate($candidateResult['user']['token']);
        $this->client->request('GET', '/api/matches/'.$match->getId());

        static::assertResponseIsSuccessful();
        static::assertJsonContains(['status' => 'viewed']);
    }

    #[Test]
    public function recruiterWithoutJobFilterSeesEmptyMatches(): void
    {
        $user = $this->registerRecruiter();
        $this->authenticate($user['token']);
        $this->client->request('GET', '/api/matches');

        static::assertResponseIsSuccessful();
        $data = $this->client->getResponse()->toArray();
        static::assertCount(0, $data['member']);
    }

    #[Test]
    public function unauthenticatedMatchAccessReturns401(): void
    {
        $this->client->request('GET', '/api/matches');

        static::assertResponseStatusCodeSame(401);
    }

    #[Test]
    public function candidateCannotAcceptOtherCandidatesMatch(): void
    {
        $candidateA = $this->createCandidateWithProfile();
        $recruiter = $this->createRecruiterWithPublishedJob();

        $match = $this->createTestMatch(
            $candidateA['profile']['id'],
            $recruiter['job']['id'],
        );

        $candidateB = $this->registerCandidate('other-candidate@test.com');
        $this->authenticate($candidateB['token']);

        $this->client->request('PATCH', '/api/matches/'.$match->getId(), [
            'json' => ['action' => 'accept'],
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);

        static::assertResponseStatusCodeSame(404);
    }

    #[Test]
    public function candidateCannotViewOtherCandidatesMatch(): void
    {
        $candidateA = $this->createCandidateWithProfile();
        $recruiter = $this->createRecruiterWithPublishedJob();

        $match = $this->createTestMatch(
            $candidateA['profile']['id'],
            $recruiter['job']['id'],
        );

        $candidateB = $this->registerCandidate('other-candidate@test.com');
        $this->authenticate($candidateB['token']);
        $this->client->request('GET', '/api/matches/'.$match->getId());

        static::assertResponseStatusCodeSame(404);
    }

    #[Test]
    public function recruiterCannotViewMatchForOtherRecruitersJob(): void
    {
        $candidate = $this->createCandidateWithProfile();
        $recruiterA = $this->createRecruiterWithPublishedJob();

        $match = $this->createTestMatch(
            $candidate['profile']['id'],
            $recruiterA['job']['id'],
        );

        $recruiterB = $this->registerRecruiter('other-recruiter@test.com');
        $this->authenticate($recruiterB['token']);
        $this->client->request('GET', '/api/matches/'.$match->getId());

        static::assertResponseStatusCodeSame(404);
    }

    #[Test]
    public function recruiterCannotAcceptMatch(): void
    {
        $candidate = $this->createCandidateWithProfile();
        $recruiter = $this->createRecruiterWithPublishedJob();

        $match = $this->createTestMatch(
            $candidate['profile']['id'],
            $recruiter['job']['id'],
        );

        $this->authenticate($recruiter['user']['token']);
        $this->client->request('PATCH', '/api/matches/'.$match->getId(), [
            'json' => ['action' => 'accept'],
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);

        static::assertResponseStatusCodeSame(403);
    }

    #[Test]
    public function candidateCannotRejectAlreadyAcceptedMatch(): void
    {
        $candidate = $this->createCandidateWithProfile();
        $recruiter = $this->createRecruiterWithPublishedJob();

        $match = $this->createTestMatch(
            $candidate['profile']['id'],
            $recruiter['job']['id'],
        );

        $this->authenticate($candidate['user']['token']);
        $this->client->request('PATCH', '/api/matches/'.$match->getId(), [
            'json' => ['action' => 'accept'],
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);
        static::assertResponseIsSuccessful();

        $this->client->request('PATCH', '/api/matches/'.$match->getId(), [
            'json' => ['action' => 'reject'],
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);

        static::assertResponseStatusCodeSame(500);
    }
}
