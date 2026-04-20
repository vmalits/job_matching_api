<?php

declare(strict_types=1);

namespace App\Tests\Integration\Auth;

use App\Tests\Integration\ApiTestCase;
use PHPUnit\Framework\Attributes\Test;

class LoginTest extends ApiTestCase
{
    #[Test]
    public function loginSuccessfully(): void
    {
        $this->registerUser('login@test.com', 'ROLE_CANDIDATE', 'password123');

        $this->client->request('POST', '/api/auth/login', [
            'json' => [
                'email' => 'login@test.com',
                'password' => 'password123',
            ],
        ]);

        static::assertResponseIsSuccessful();
        $data = $this->client->getResponse()->toArray();
        static::assertArrayHasKey('token', $data);
        static::assertNotEmpty($data['token']);
    }

    #[Test]
    public function loginWithWrongPasswordFails(): void
    {
        $this->registerUser('wrong-pw@test.com', 'ROLE_CANDIDATE', 'password123');

        $this->client->request('POST', '/api/auth/login', [
            'json' => [
                'email' => 'wrong-pw@test.com',
                'password' => 'wrong-password',
            ],
        ]);

        static::assertResponseStatusCodeSame(401);
    }

    #[Test]
    public function loginWithNonexistentEmailFails(): void
    {
        $this->client->request('POST', '/api/auth/login', [
            'json' => [
                'email' => 'nonexistent@test.com',
                'password' => 'password123',
            ],
        ]);

        static::assertResponseStatusCodeSame(401);
    }

    #[Test]
    public function loginTokenGrantsApiAccess(): void
    {
        $user = $this->registerUser('login-access@test.com', 'ROLE_CANDIDATE', 'password123');

        $this->client->request('POST', '/api/auth/login', [
            'json' => [
                'email' => 'login-access@test.com',
                'password' => 'password123',
            ],
        ]);

        $loginToken = $this->client->getResponse()->toArray()['token'];

        $this->authenticate($loginToken);
        $this->client->request('GET', '/api/users/'.$user['id']);

        static::assertResponseIsSuccessful();
    }
}
