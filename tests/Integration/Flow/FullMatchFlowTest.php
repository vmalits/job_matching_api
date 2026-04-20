<?php

declare(strict_types=1);

namespace App\Tests\Integration\Flow;

use App\Tests\Integration\ApiTestCase;
use PHPUnit\Framework\Attributes\Test;

class FullMatchFlowTest extends ApiTestCase
{
    #[Test]
    public function completeMatchFlowScenario(): void
    {
        $recruiter = $this->registerRecruiter('flow-recruiter@test.com');
        static::assertNotEmpty($recruiter['token']);

        $this->authenticate($recruiter['token']);
        $this->client->request('POST', '/api/jobs', ['json' => [
            'title' => 'Senior Symfony Developer',
            'description' => 'Join our team',
            'companyName' => 'TechCorp',
            'location' => 'Berlin, Germany',
            'employmentType' => 'full_time',
            'skills' => ['PHP', 'Symfony', 'Docker'],
            'salaryMin' => 90000,
            'salaryMax' => 130000,
            'salaryVisible' => true,
            'status' => 'published',
        ]]);
        static::assertResponseIsSuccessful();
        $job = $this->client->getResponse()->toArray();
        static::assertSame('published', $job['status']);

        $candidate = $this->registerCandidate('flow-candidate@test.com');
        static::assertNotEmpty($candidate['token']);

        $this->authenticate($candidate['token']);
        $this->client->request('POST', '/api/candidate_profiles', ['json' => [
            'title' => 'PHP/Symfony Developer',
            'bio' => '5 years of Symfony experience',
            'location' => 'Berlin, Germany',
            'experienceYears' => 5,
            'skills' => ['PHP', 'Symfony', 'Docker', 'MySQL'],
            'salaryMin' => 95000,
            'salaryMax' => 125000,
        ]]);
        static::assertResponseIsSuccessful();
        $profile = $this->client->getResponse()->toArray();
        static::assertSame('PHP/Symfony Developer', $profile['title']);

        $match = $this->createTestMatch($profile['id'], $job['id'], 92);

        $this->client->request('GET', '/api/matches');
        $matchesData = $this->client->getResponse()->toArray();
        static::assertCount(1, $matchesData['member']);
        $matchData = $matchesData['member'][0];
        static::assertSame($job['title'], $matchData['jobTitle']);
        static::assertSame($job['companyName'], $matchData['jobCompanyName']);

        $this->client->request('PATCH', '/api/matches/'.$match->getId(), [
            'json' => ['action' => 'accept'],
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);
        static::assertResponseIsSuccessful();
        static::assertJsonContains(['status' => 'accepted']);

        $this->authenticate($recruiter['token']);
        $this->client->request('GET', '/api/matches?jobId='.$job['id']);
        $recruiterMatches = $this->client->getResponse()->toArray();
        static::assertCount(1, $recruiterMatches['member']);
        static::assertSame($candidate['email'], $recruiterMatches['member'][0]['candidateEmail']);

        $this->client->request('GET', '/api/jobs');
        $jobsData = $this->client->getResponse()->toArray();
        static::assertCount(1, $jobsData['member']);
        static::assertSame('Senior Symfony Developer', $jobsData['member'][0]['title']);
    }
}
