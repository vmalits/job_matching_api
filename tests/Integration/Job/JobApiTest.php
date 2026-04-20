<?php

declare(strict_types=1);

namespace App\Tests\Integration\Job;

use App\Tests\Integration\ApiTestCase;
use PHPUnit\Framework\Attributes\Test;

class JobApiTest extends ApiTestCase
{
    #[Test]
    public function recruiterCanCreateJob(): void
    {
        $user = $this->registerRecruiter();
        $this->authenticate($user['token']);
        $this->client->request('POST', '/api/jobs', [
            'json' => [
                'title' => 'PHP Developer',
                'description' => 'We need a developer',
                'companyName' => 'Acme Corp',
                'location' => 'Berlin, Germany',
                'employmentType' => 'full_time',
                'skills' => ['PHP', 'Symfony'],
                'salaryMin' => 80000,
                'salaryMax' => 120000,
                'salaryVisible' => true,
            ],
        ]);

        static::assertResponseIsSuccessful();
        static::assertJsonContains([
            'title' => 'PHP Developer',
            'description' => 'We need a developer',
            'companyName' => 'Acme Corp',
            'location' => 'Berlin, Germany',
            'employmentType' => 'full_time',
            'skills' => ['PHP', 'Symfony'],
            'salaryMin' => 80000,
            'salaryMax' => 120000,
            'salaryVisible' => true,
            'status' => 'draft',
            'recruiterId' => $user['id'],
            'recruiterEmail' => $user['email'],
        ]);

        $data = $this->client->getResponse()->toArray();
        static::assertArrayHasKey('id', $data);
        static::assertArrayHasKey('createdAt', $data);
    }

    #[Test]
    public function recruiterCanPublishJob(): void
    {
        $user = $this->registerRecruiter();
        $this->authenticate($user['token']);
        $this->client->request('POST', '/api/jobs', [
            'json' => [
                'title' => 'PHP Developer',
                'description' => 'We need a developer',
                'companyName' => 'Acme Corp',
                'location' => 'Berlin, Germany',
                'employmentType' => 'full_time',
            ],
        ]);
        $job = $this->client->getResponse()->toArray();

        $this->client->request('PATCH', '/api/jobs/'.$job['id'], [
            'json' => ['status' => 'published'],
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);

        static::assertResponseIsSuccessful();
        static::assertJsonContains(['status' => 'published']);
    }

    #[Test]
    public function recruiterCanCloseJob(): void
    {
        $user = $this->registerRecruiter();
        $this->authenticate($user['token']);
        $this->client->request('POST', '/api/jobs', [
            'json' => [
                'title' => 'PHP Developer',
                'description' => 'We need a developer',
                'companyName' => 'Acme Corp',
                'location' => 'Berlin',
                'employmentType' => 'full_time',
                'status' => 'published',
            ],
        ]);
        $job = $this->client->getResponse()->toArray();

        $this->client->request('PATCH', '/api/jobs/'.$job['id'], [
            'json' => ['status' => 'closed'],
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);

        static::assertResponseIsSuccessful();
        static::assertJsonContains(['status' => 'closed']);
    }

    #[Test]
    public function recruiterSeesOnlyOwnJobs(): void
    {
        $recruiterA = $this->registerRecruiter('recruiter-a@test.com');
        $this->authenticate($recruiterA['token']);
        $this->client->request('POST', '/api/jobs', ['json' => [
            'title' => 'Job A1',
            'description' => 'Desc',
            'companyName' => 'Corp A',
            'location' => 'Berlin',
            'employmentType' => 'full_time',
        ]]);
        $this->client->request('POST', '/api/jobs', ['json' => [
            'title' => 'Job A2',
            'description' => 'Desc',
            'companyName' => 'Corp A',
            'location' => 'Berlin',
            'employmentType' => 'full_time',
        ]]);

        $recruiterB = $this->registerRecruiter('recruiter-b@test.com');
        $this->authenticate($recruiterB['token']);
        $this->client->request('POST', '/api/jobs', ['json' => [
            'title' => 'Job B1',
            'description' => 'Desc',
            'companyName' => 'Corp B',
            'location' => 'Munich',
            'employmentType' => 'part_time',
        ]]);

        $this->authenticate($recruiterA['token']);
        $this->client->request('GET', '/api/jobs');
        static::assertResponseIsSuccessful();
        $data = $this->client->getResponse()->toArray();
        static::assertCount(2, $data['member']);
    }

    #[Test]
    public function candidateSeesOnlyPublishedJobs(): void
    {
        $recruiter = $this->registerRecruiter();
        $this->authenticate($recruiter['token']);

        $this->client->request('POST', '/api/jobs', ['json' => [
            'title' => 'Draft Job',
            'description' => 'Desc',
            'companyName' => 'Corp',
            'location' => 'Berlin',
            'employmentType' => 'full_time',
        ]]);

        $this->client->request('POST', '/api/jobs', ['json' => [
            'title' => 'Published Job',
            'description' => 'Desc',
            'companyName' => 'Corp',
            'location' => 'Berlin',
            'employmentType' => 'full_time',
            'status' => 'published',
        ]]);

        $candidate = $this->registerCandidate();
        $this->authenticate($candidate['token']);
        $this->client->request('GET', '/api/jobs');

        static::assertResponseIsSuccessful();
        $data = $this->client->getResponse()->toArray();
        static::assertCount(1, $data['member']);
        static::assertSame('Published Job', $data['member'][0]['title']);
    }

    #[Test]
    public function candidateCannotCreateJob(): void
    {
        $user = $this->registerCandidate();
        $this->authenticate($user['token']);
        $this->client->request('POST', '/api/jobs', [
            'json' => [
                'title' => 'Job',
                'description' => 'Desc',
                'companyName' => 'Corp',
                'location' => 'Berlin',
                'employmentType' => 'full_time',
            ],
        ]);

        static::assertResponseStatusCodeSame(403);
    }

    #[Test]
    public function recruiterCannotEditOtherRecruitersJob(): void
    {
        $recruiterA = $this->registerRecruiter('owner@test.com');
        $this->authenticate($recruiterA['token']);
        $this->client->request('POST', '/api/jobs', ['json' => [
            'title' => 'Job',
            'description' => 'Desc',
            'companyName' => 'Corp',
            'location' => 'Berlin',
            'employmentType' => 'full_time',
        ]]);
        $job = $this->client->getResponse()->toArray();

        $recruiterB = $this->registerRecruiter('other@test.com');
        $this->authenticate($recruiterB['token']);
        $this->client->request('PATCH', '/api/jobs/'.$job['id'], [
            'json' => ['title' => 'Hacked'],
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ]);

        static::assertResponseStatusCodeSame(404);
    }

    #[Test]
    public function candidateCannotSeeDraftJobs(): void
    {
        $recruiter = $this->registerRecruiter();
        $this->authenticate($recruiter['token']);
        $this->client->request('POST', '/api/jobs', ['json' => [
            'title' => 'Draft Job',
            'description' => 'Desc',
            'companyName' => 'Corp',
            'location' => 'Berlin',
            'employmentType' => 'full_time',
        ]]);
        $job = $this->client->getResponse()->toArray();

        $candidate = $this->registerCandidate();
        $this->authenticate($candidate['token']);
        $this->client->request('GET', '/api/jobs/'.$job['id']);

        static::assertResponseStatusCodeSame(404);
    }

    #[Test]
    public function unauthenticatedJobAccessReturns401(): void
    {
        $this->client->request('GET', '/api/jobs');

        static::assertResponseStatusCodeSame(401);
    }

    #[Test]
    public function salaryHiddenWhenNotVisible(): void
    {
        $user = $this->registerRecruiter();
        $this->authenticate($user['token']);
        $this->client->request('POST', '/api/jobs', [
            'json' => [
                'title' => 'Secret Salary Job',
                'description' => 'Desc',
                'companyName' => 'Corp',
                'location' => 'Berlin',
                'employmentType' => 'full_time',
                'salaryMin' => 80000,
                'salaryMax' => 120000,
                'salaryVisible' => false,
                'status' => 'published',
            ],
        ]);
        $job = $this->client->getResponse()->toArray();

        $candidate = $this->registerCandidate();
        $this->authenticate($candidate['token']);
        $this->client->request('GET', '/api/jobs/'.$job['id']);

        static::assertResponseIsSuccessful();
        $data = $this->client->getResponse()->toArray();
        static::assertNull($data['salaryMin']);
        static::assertNull($data['salaryMax']);
        static::assertFalse($data['salaryVisible']);
    }

    #[Test]
    public function recruiterSeesOwnSalaryEvenWhenNotVisible(): void
    {
        $user = $this->registerRecruiter();
        $this->authenticate($user['token']);
        $this->client->request('POST', '/api/jobs', [
            'json' => [
                'title' => 'Secret Salary Job',
                'description' => 'Desc',
                'companyName' => 'Corp',
                'location' => 'Berlin',
                'employmentType' => 'full_time',
                'salaryMin' => 80000,
                'salaryMax' => 120000,
                'salaryVisible' => false,
            ],
        ]);
        $job = $this->client->getResponse()->toArray();

        $this->client->request('GET', '/api/jobs/'.$job['id']);

        static::assertResponseIsSuccessful();
        $data = $this->client->getResponse()->toArray();
        static::assertSame(80000, $data['salaryMin']);
        static::assertSame(120000, $data['salaryMax']);
        static::assertFalse($data['salaryVisible']);
    }
}
