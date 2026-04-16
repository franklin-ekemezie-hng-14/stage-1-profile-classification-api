<?php

namespace App\OpenApi\Profiles;

use OpenApi\Attributes as OA;

class CreateProfileEndpoint
{
    #[OA\Post(
        path: '/api/profiles',
        operationId: 'createProfile',
        tags: ['Profiles'],
        summary: 'Create a profile',
        description: 'Accepts a name, calls upstream classification APIs, persists the profile, and returns the stored result. If the profile already exists, the existing profile is returned.',
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Profile creation payload',
            content: new OA\JsonContent(ref: '#/components/schemas/CreateProfileRequest')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Profile created successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/ProfileResponse')
            ),
            new OA\Response(
                response: 200,
                description: 'Profile already exists',
                content: new OA\JsonContent(ref: '#/components/schemas/ExistingProfileResponse')
            ),
            new OA\Response(
                response: 400,
                description: 'Missing or empty name',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid request type',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 502,
                description: 'Upstream service returned an invalid response',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function __invoke(): void {}
}
