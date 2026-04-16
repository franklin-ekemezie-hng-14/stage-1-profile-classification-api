<?php

namespace App\OpenApi\Profiles;

use OpenApi\Attributes as OA;

class DeleteProfileEndpoint
{
    #[OA\Delete(
        path: '/api/profiles/{id}',
        operationId: 'deleteProfile',
        tags: ['Profiles'],
        summary: 'Delete a profile',
        description: 'Deletes a stored profile by UUID.',
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
                response: 204,
                description: 'Profile deleted successfully'
            ),
            new OA\Response(
                response: 404,
                description: 'Profile not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 500,
                description: 'Profile deletion failed',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function __invoke(): void {}
}
