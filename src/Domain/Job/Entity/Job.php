<?php

declare(strict_types=1);

namespace App\Domain\Job\Entity;

use App\Domain\Job\Enum\EmploymentType;
use App\Domain\Job\Enum\JobStatus;
use Symfony\Component\Uid\Uuid;

class Job
{
    private string $id;
    private string $recruiterId;
    private string $title;
    private string $description;
    private string $companyName;
    private string $location;
    private EmploymentType $employmentType;
    private JobStatus $status;
    /** @var list<string> */
    private array $skills;
    private ?int $salaryMin = null;
    private ?int $salaryMax = null;
    private bool $salaryVisible = false;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    /**
     * @param list<string> $skills
     */
    public function __construct(
        string $recruiterId,
        string $title,
        string $description,
        string $companyName,
        string $location,
        EmploymentType $employmentType,
        array $skills = [],
    ) {
        $this->id = Uuid::v7()->toRfc4122();
        $this->recruiterId = $recruiterId;
        $this->title = $title;
        $this->description = $description;
        $this->companyName = $companyName;
        $this->location = $location;
        $this->employmentType = $employmentType;
        $this->skills = $skills;
        $this->status = JobStatus::DRAFT;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getRecruiterId(): string
    {
        return $this->recruiterId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): static
    {
        $this->companyName = $companyName;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getEmploymentType(): EmploymentType
    {
        return $this->employmentType;
    }

    public function setEmploymentType(EmploymentType $employmentType): static
    {
        $this->employmentType = $employmentType;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getStatus(): JobStatus
    {
        return $this->status;
    }

    public function publish(): static
    {
        $this->status = JobStatus::PUBLISHED;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function close(): static
    {
        $this->status = JobStatus::CLOSED;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function archive(): static
    {
        $this->status = JobStatus::ARCHIVED;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function isPublished(): bool
    {
        return JobStatus::PUBLISHED === $this->status;
    }

    /**
     * @return list<string>
     */
    public function getSkills(): array
    {
        return $this->skills;
    }

    /**
     * @param list<string> $skills
     */
    public function setSkills(array $skills): static
    {
        $this->skills = $skills;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getSalaryMin(): ?int
    {
        return $this->salaryMin;
    }

    public function setSalaryMin(?int $salaryMin): static
    {
        $this->salaryMin = $salaryMin;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getSalaryMax(): ?int
    {
        return $this->salaryMax;
    }

    public function setSalaryMax(?int $salaryMax): static
    {
        $this->salaryMax = $salaryMax;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function isSalaryVisible(): bool
    {
        return $this->salaryVisible;
    }

    public function setSalaryVisible(bool $salaryVisible): static
    {
        $this->salaryVisible = $salaryVisible;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
