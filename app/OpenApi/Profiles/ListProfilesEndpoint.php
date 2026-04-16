<?php

namespace App\OpenApi\Profiles;

use OpenApi\Attributes as OA;

class ListProfilesEndpoint
{
    #[OA\Get(
        path: '/api/profiles',
        operationId: 'listProfiles',
        tags: ['Profiles'],
        summary: 'List stored profiles',
        description: 'Returns all stored profiles. Supports case-insensitive filtering by gender, country_id, and age_group.',
        parameters: [
            new OA\Parameter(
                name: 'gender',
                description: 'Filter profiles by gender. Case-insensitive.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'male')
            ),
            new OA\Parameter(
                name: 'country_id',
                description: 'Filter profiles by ISO country code. Case-insensitive.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'ng')
            ),
            new OA\Parameter(
                name: 'age_group',
                description: 'Filter profiles by derived age group. Case-insensitive.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'adult')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profiles retrieved successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/ProfilesListResponse')
            ),
        ]
    )]
    public function __invoke(): void {}
}
