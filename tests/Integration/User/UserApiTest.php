<?php

declare(strict_types=1);

namespace App\Tests\Integration\User;

use App\Tests\Integration\ApiTestCase;
use PHPUnit\Framework\Attributes\Test;

class UserApiTest extends ApiTestCase
{
    #[Test]
    public function getOwnUserProfile(): void
    {
        $user = $this->registerUser('profile@test.com', 'ROLE_CANDIDATE');
        $this->authenticate($user['token']);
        $this->client->request('GET', '/api/users/'.$user['id']);

        static::assertResponseIsSuccessful();
        static::assertJsonContains([
            'id' => $user['id'],
            'email' => 'profile@test.com',
            'firstName' => 'Test',
            'lastName' => 'User',
            'fullName' => 'Test User',
        ]);

        $data = $this->client->getResponse()->toArray();
        static::assertArrayNotHasKey('password', $data);
        static::assertArrayNotHasKey('token', $data);
    }

    #[Test]
    public function getOtherUserProfileReturns404(): void
    {
        $userA = $this->registerUser('user-a@test.com', 'ROLE_CANDIDATE');
        $userB = $this->registerUser('user-b@test.com', 'ROLE_CANDIDATE');

        $this->authenticate($userA['token']);
        $this->client->request('GET', '/api/users/'.$userB['id']);

        static::assertResponseStatusCodeSame(404);
    }

    #[Test]
    public function getUserWithoutAuthReturns401(): void
    {
        $this->client->request('GET', '/api/users/some-id');

        static::assertResponseStatusCodeSame(401);
    }
}
