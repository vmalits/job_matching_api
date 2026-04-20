<?php

declare(strict_types=1);

namespace App\Tests\Integration\Auth;

use App\Tests\Integration\ApiTestCase;
use PHPUnit\Framework\Attributes\Test;

class RegistrationTest extends ApiTestCase
{
    #[Test]
    public function registerCandidateSuccessfully(): void
    {
        $this->client->request('POST', '/api/auth/register', [
            'json' => [
                'email' => 'new-candidate@test.com',
                'password' => 'password123',
                'firstName' => 'John',
                'lastName' => 'Doe',
                'role' => 'ROLE_CANDIDATE',
            ],
        ]);

        static::assertResponseIsSuccessful();
        static::assertJsonContains([
            'email' => 'new-candidate@test.com',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'fullName' => 'John Doe',
        ]);

        $data = $this->client->getResponse()->toArray();
        static::assertArrayHasKey('id', $data);
        static::assertArrayHasKey('token', $data);
        static::assertArrayHasKey('createdAt', $data);
        static::assertNotEmpty($data['token']);
        static::assertContains('ROLE_CANDIDATE', $data['roles']);
        static::assertContains('ROLE_USER', $data['roles']);
        static::assertArrayNotHasKey('password', $data);
    }

    #[Test]
    public function registerRecruiterSuccessfully(): void
    {
        $this->client->request('POST', '/api/auth/register', [
            'json' => [
                'email' => 'new-recruiter@test.com',
                'password' => 'password123',
                'firstName' => 'Jane',
                'lastName' => 'Smith',
                'role' => 'ROLE_RECRUITER',
            ],
        ]);

        static::assertResponseIsSuccessful();
        static::assertJsonContains([
            'email' => 'new-recruiter@test.com',
            'firstName' => 'Jane',
            'lastName' => 'Smith',
        ]);

        $data = $this->client->getResponse()->toArray();
        static::assertContains('ROLE_RECRUITER', $data['roles']);
        static::assertContains('ROLE_USER', $data['roles']);
    }

    #[Test]
    public function registerReturnsValidJwtToken(): void
    {
        $user = $this->registerUser('token-test@test.com', 'ROLE_CANDIDATE');

        $this->authenticate($user['token']);
        $this->client->request('GET', '/api/users/'.$user['id']);

        static::assertResponseIsSuccessful();
    }

    #[Test]
    public function registerDuplicateEmailFails(): void
    {
        $this->registerUser('duplicate@test.com', 'ROLE_CANDIDATE');

        $this->client->request('POST', '/api/auth/register', [
            'json' => [
                'email' => 'duplicate@test.com',
                'password' => 'password123',
                'firstName' => 'Test',
                'lastName' => 'User',
                'role' => 'ROLE_CANDIDATE',
            ],
        ]);

        static::assertResponseStatusCodeSame(409);
        $data = $this->client->getResponse()->toArray(false);
        static::assertStringContainsString('already exists', $data['detail']);
    }

    #[Test]
    public function registerWithBlankEmailFails(): void
    {
        $this->client->request('POST', '/api/auth/register', [
            'json' => [
                'email' => '',
                'password' => 'password123',
                'firstName' => 'Test',
                'lastName' => 'User',
                'role' => 'ROLE_CANDIDATE',
            ],
        ]);

        static::assertResponseStatusCodeSame(422);
        $data = $this->client->getResponse()->toArray(false);
        static::assertStringContainsString('email', $data['detail'] ?? $data['hydra:description'] ?? '');
    }

    #[Test]
    public function registerWithInvalidEmailFails(): void
    {
        $this->client->request('POST', '/api/auth/register', [
            'json' => [
                'email' => 'not-an-email',
                'password' => 'password123',
                'firstName' => 'Test',
                'lastName' => 'User',
                'role' => 'ROLE_CANDIDATE',
            ],
        ]);

        static::assertResponseStatusCodeSame(422);
        $data = $this->client->getResponse()->toArray(false);
        static::assertStringContainsString('email', $data['detail'] ?? $data['hydra:description'] ?? '');
    }

    #[Test]
    public function registerWithShortPasswordFails(): void
    {
        $this->client->request('POST', '/api/auth/register', [
            'json' => [
                'email' => 'short-pw@test.com',
                'password' => '12345',
                'firstName' => 'Test',
                'lastName' => 'User',
                'role' => 'ROLE_CANDIDATE',
            ],
        ]);

        static::assertResponseStatusCodeSame(422);
        $data = $this->client->getResponse()->toArray(false);
        static::assertStringContainsString('password', $data['detail'] ?? $data['hydra:description'] ?? '');
    }

    #[Test]
    public function registerWithInvalidRoleFails(): void
    {
        $this->client->request('POST', '/api/auth/register', [
            'json' => [
                'email' => 'bad-role@test.com',
                'password' => 'password123',
                'firstName' => 'Test',
                'lastName' => 'User',
                'role' => 'ROLE_ADMIN',
            ],
        ]);

        static::assertResponseStatusCodeSame(422);
        $data = $this->client->getResponse()->toArray(false);
        static::assertStringContainsString('role', $data['detail'] ?? $data['hydra:description'] ?? '');
    }
}
