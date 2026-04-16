<?php

declare(strict_types=1);

namespace App\Tests\Domain\User;

use App\Domain\User\Entity\User;
use App\Domain\User\Enum\Role;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private User $candidate;
    private User $recruiter;

    protected function setUp(): void
    {
        $this->candidate = new User(
            email: 'candidate@test.com',
            firstName: 'Ivan',
            lastName: 'Ivanov',
            role: Role::CANDIDATE,
        );

        $this->recruiter = new User(
            email: 'recruiter@test.com',
            firstName: 'Petr',
            lastName: 'Petrov',
            role: Role::RECRUITER,
        );
    }

    #[Test]
    public function itCreatesUserWithId(): void
    {
        $this->assertNotEmpty($this->candidate->getId());
        $this->assertSame(36, \strlen($this->candidate->getId()));
    }

    #[Test]
    public function itReturnsEmail(): void
    {
        $this->assertSame('candidate@test.com', $this->candidate->getEmail());
    }

    #[Test]
    public function itReturnsUserIdentifier(): void
    {
        $this->assertSame('candidate@test.com', $this->candidate->getUserIdentifier());
    }

    #[Test]
    public function itReturnsFullName(): void
    {
        $this->assertSame('Ivan Ivanov', $this->candidate->getFullName());
    }

    #[Test]
    public function candidateHasCandidateRole(): void
    {
        $this->assertTrue($this->candidate->isCandidate());
        $this->assertFalse($this->candidate->isRecruiter());
    }

    #[Test]
    public function recruiterHasRecruiterRole(): void
    {
        $this->assertTrue($this->recruiter->isRecruiter());
        $this->assertFalse($this->recruiter->isCandidate());
    }

    #[Test]
    public function itReturnsRolesWithRoleUser(): void
    {
        $roles = $this->candidate->getRoles();

        $this->assertContains('ROLE_CANDIDATE', $roles);
        $this->assertContains('ROLE_USER', $roles);
        $this->assertCount(2, $roles);
    }

    #[Test]
    public function itReturnsPrimaryRole(): void
    {
        $this->assertSame(Role::CANDIDATE, $this->candidate->getPrimaryRole());
        $this->assertSame(Role::RECRUITER, $this->recruiter->getPrimaryRole());
    }

    #[Test]
    public function itHasDomainRoles(): void
    {
        $roles = $this->candidate->getDomainRoles();

        $this->assertCount(1, $roles);
        $this->assertSame(Role::CANDIDATE, $roles[0]);
    }

    #[Test]
    public function itChecksRolePresence(): void
    {
        $this->assertTrue($this->candidate->hasRole(Role::CANDIDATE));
        $this->assertFalse($this->candidate->hasRole(Role::RECRUITER));
    }

    #[Test]
    public function itSetsHashedPassword(): void
    {
        $this->candidate->setPassword('hashed_password');

        $this->assertSame('hashed_password', $this->candidate->getPassword());
    }

    #[Test]
    public function passwordIsNullByDefault(): void
    {
        $this->assertNull($this->candidate->getPassword());
    }

    #[Test]
    public function itUpdatesTimestampOnSetEmail(): void
    {
        $original = $this->candidate->getUpdatedAt();
        usleep(1000);
        $this->candidate->setEmail('new@test.com');

        $this->assertSame('new@test.com', $this->candidate->getEmail());
        $this->assertGreaterThan($original, $this->candidate->getUpdatedAt());
    }

    #[Test]
    public function itThrowsOnEmptyEmailForUserIdentifier(): void
    {
        $user = new User('', 'A', 'B', Role::CANDIDATE);

        $this->expectException(\LogicException::class);
        $user->getUserIdentifier();
    }

    #[Test]
    public function itCreatesUniqueIds(): void
    {
        $another = new User('other@test.com', 'A', 'B', Role::CANDIDATE);

        $this->assertNotSame($this->candidate->getId(), $another->getId());
    }
}
