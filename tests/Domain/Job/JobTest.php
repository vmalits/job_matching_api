<?php

declare(strict_types=1);

namespace App\Tests\Domain\Job;

use App\Domain\Job\Entity\Job;
use App\Domain\Job\Enum\EmploymentType;
use App\Domain\Job\Enum\JobStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class JobTest extends TestCase
{
    private Job $job;

    protected function setUp(): void
    {
        $this->job = new Job(
            recruiterId: 'recruiter-123',
            title: 'Senior Developer',
            description: 'Job description',
            companyName: 'TechCorp',
            location: 'Berlin, Germany',
            employmentType: EmploymentType::FULL_TIME,
            skills: ['PHP', 'Symfony', 'Docker'],
        );
    }

    #[Test]
    public function itCreatesJobInDraftStatus(): void
    {
        $this->assertSame(JobStatus::DRAFT, $this->job->getStatus());
        $this->assertFalse($this->job->isPublished());
    }

    #[Test]
    public function itPublishesJob(): void
    {
        $this->job->publish();

        $this->assertSame(JobStatus::PUBLISHED, $this->job->getStatus());
        $this->assertTrue($this->job->isPublished());
    }

    #[Test]
    public function itClosesJob(): void
    {
        $this->job->publish();
        $this->job->close();

        $this->assertSame(JobStatus::CLOSED, $this->job->getStatus());
    }

    #[Test]
    public function itArchivesJob(): void
    {
        $this->job->publish();
        $this->job->close();
        $this->job->archive();

        $this->assertSame(JobStatus::ARCHIVED, $this->job->getStatus());
    }

    #[Test]
    public function itHasSkills(): void
    {
        $this->assertSame(['PHP', 'Symfony', 'Docker'], $this->job->getSkills());
    }

    #[Test]
    public function itUpdatesSkills(): void
    {
        $this->job->setSkills(['PHP', 'Go']);

        $this->assertSame(['PHP', 'Go'], $this->job->getSkills());
    }

    #[Test]
    public function itHasSalaryDefaults(): void
    {
        $this->assertNull($this->job->getSalaryMin());
        $this->assertNull($this->job->getSalaryMax());
        $this->assertFalse($this->job->isSalaryVisible());
    }

    #[Test]
    public function itSetsSalary(): void
    {
        $this->job->setSalaryMin(50000);
        $this->job->setSalaryMax(80000);
        $this->job->setSalaryVisible(true);

        $this->assertSame(50000, $this->job->getSalaryMin());
        $this->assertSame(80000, $this->job->getSalaryMax());
        $this->assertTrue($this->job->isSalaryVisible());
    }

    #[Test]
    public function itReturnsEmploymentType(): void
    {
        $this->assertSame(EmploymentType::FULL_TIME, $this->job->getEmploymentType());
    }

    #[Test]
    public function itUpdatesTitle(): void
    {
        $this->job->setTitle('Lead Developer');

        $this->assertSame('Lead Developer', $this->job->getTitle());
    }

    #[Test]
    public function itHasTimestamps(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->job->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->job->getUpdatedAt());
    }
}
