<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class HealthEndpoint
{
    #[OA\Get(
        path: '/',
        operationId: 'healthCheck',
        tags: ['Health'],
        summary: 'Check API health',
        description: 'Returns a lightweight status payload confirming that the API is running.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'API is healthy',
                content: new OA\JsonContent(ref: '#/components/schemas/HealthResponse')
            ),
        ]
    )]
    public function __invoke(): void {}
}
