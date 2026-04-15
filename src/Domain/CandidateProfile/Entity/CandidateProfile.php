<?php

declare(strict_types=1);

namespace App\Domain\CandidateProfile\Entity;

class CandidateProfile
{
    private string $id;
    private string $userId;
    private string $title;
    private string $bio;
    private string $location;
    private int $experienceYears;
    /** @var list<string> */
    private array $skills;
    private ?int $salaryMin = null;
    private ?int $salaryMax = null;
    private ?string $resumeUrl = null;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    /**
     * @param list<string> $skills
     */
    public function __construct(
        string $userId,
        string $title,
        string $bio,
        string $location,
        int $experienceYears,
        array $skills = [],
    ) {
        $this->id = \Symfony\Component\Uid\Uuid::v7()->toRfc4122();
        $this->userId = $userId;
        $this->title = $title;
        $this->bio = $bio;
        $this->location = $location;
        $this->experienceYears = $experienceYears;
        $this->skills = $skills;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->userId;
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

    public function getBio(): string
    {
        return $this->bio;
    }

    public function setBio(string $bio): static
    {
        $this->bio = $bio;
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

    public function getExperienceYears(): int
    {
        return $this->experienceYears;
    }

    public function setExperienceYears(int $experienceYears): static
    {
        $this->experienceYears = $experienceYears;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
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

    public function getResumeUrl(): ?string
    {
        return $this->resumeUrl;
    }

    public function setResumeUrl(?string $resumeUrl): static
    {
        $this->resumeUrl = $resumeUrl;
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
