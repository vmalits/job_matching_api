<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model;
use ApiPlatform\OpenApi\OpenApi;

final readonly class JwtLoginDecorator implements OpenApiFactoryInterface
{
    public function __construct(
        private OpenApiFactoryInterface $decorated,
    ) {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);

        $schemas = $openApi->getComponents()->getSchemas();
        $schemas['Credentials'] = new \ArrayObject([
            'type' => 'object',
            'properties' => [
                'email' => ['type' => 'string', 'example' => 'user@example.com'],
                'password' => ['type' => 'string', 'example' => 'pass123'],
            ],
            'required' => ['email', 'password'],
        ]);
        $schemas['Token'] = new \ArrayObject([
            'type' => 'object',
            'properties' => [
                'token' => ['type' => 'string', 'example' => 'eyJhbGciOiJIUzI1NiJ9...'],
            ],
        ]);

        $loginPath = '/api/auth/login';
        $openApi->getPaths()->addPath($loginPath, new Model\PathItem(
            ref: null,
            get: null,
            put: null,
            post: new Model\Operation(
                operationId: 'postLogin',
                tags: ['Auth'],
                responses: [
                    '200' => new Model\Response(
                        description: 'JWT token',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/Token'],
                            ],
                        ]),
                    ),
                    '401' => new Model\Response(description: 'Invalid credentials'),
                ],
                summary: 'Get JWT token',
                requestBody: new Model\RequestBody(
                    description: 'Credentials',
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/Credentials'],
                        ],
                    ]),
                ),
            ),
        ));

        return $openApi;
    }
}
