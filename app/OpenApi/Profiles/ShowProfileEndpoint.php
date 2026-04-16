<?php

namespace App\OpenApi\Profiles;

use OpenApi\Attributes as OA;

class ShowProfileEndpoint
{
    #[OA\Get(
        path: '/api/profiles/{id}',
        operationId: 'showProfile',
        tags: ['Profiles'],
        summary: 'Get a single profile',
        description: 'Returns a stored profile by UUID.',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Profile UUID.',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid', example: '0196354c-c51f-7b79-b5d0-72245f52f001')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profile retrieved successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/ProfileResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Profile not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function __invoke(): void {}
}
