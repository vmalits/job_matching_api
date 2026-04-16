<?php

declare(strict_types=1);

namespace App\Tests\Domain\User;

use App\Domain\User\Enum\Role;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    #[Test]
    public function itHasCandidateRole(): void
    {
        $this->assertSame('ROLE_CANDIDATE', Role::CANDIDATE->value);
    }

    #[Test]
    public function itHasRecruiterRole(): void
    {
        $this->assertSame('ROLE_RECRUITER', Role::RECRUITER->value);
    }

    #[Test]
    public function itReturnsAllValues(): void
    {
        $values = Role::values();

        $this->assertCount(2, $values);
        $this->assertContains(Role::CANDIDATE, $values);
        $this->assertContains(Role::RECRUITER, $values);
    }

    #[Test]
    public function itReturnsLabels(): void
    {
        $this->assertSame('Candidate', Role::CANDIDATE->label());
        $this->assertSame('Recruiter', Role::RECRUITER->label());
    }

    #[Test]
    public function itCreatesFromValue(): void
    {
        $role = Role::from('ROLE_CANDIDATE');

        $this->assertSame(Role::CANDIDATE, $role);
    }
}
