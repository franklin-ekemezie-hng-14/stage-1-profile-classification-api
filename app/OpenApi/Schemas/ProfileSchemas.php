<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CreateProfileRequest',
    required: ['name'],
    properties: [
        new OA\Property(
            property: 'name',
            type: 'string',
            description: 'Name to classify and persist.',
            example: 'ella'
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'Profile',
    required: ['id', 'name', 'gender', 'gender_probability', 'sample_size', 'age', 'age_group', 'country_id', 'country_probability', 'created_at'],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid', example: '0196354c-c51f-7b79-b5d0-72245f52f001', description: 'Profile UUID.'),
        new OA\Property(property: 'name', type: 'string', example: 'ella', description: 'Normalized profile name.'),
        new OA\Property(property: 'gender', type: 'string', example: 'female', description: 'Predicted gender from Genderize.'),
        new OA\Property(property: 'gender_probability', type: 'number', format: 'float', example: 0.99, description: 'Prediction confidence returned by Genderize.'),
        new OA\Property(property: 'sample_size', type: 'integer', example: 1234, description: 'Sample size returned by upstream classification services.'),
        new OA\Property(property: 'age', type: 'integer', example: 46, description: 'Predicted age from Agify.'),
        new OA\Property(property: 'age_group', type: 'string', example: 'adult', description: 'Derived age group classification.'),
        new OA\Property(property: 'country_id', type: 'string', example: 'DRC', description: 'Most likely country code from Nationalize.'),
        new OA\Property(property: 'country_probability', type: 'number', format: 'float', example: 0.85, description: 'Probability of the selected country.'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-04-15T12:00:00Z', description: 'Creation timestamp in ISO 8601 UTC.'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'ProfileListItem',
    required: ['id', 'name', 'gender', 'age', 'age_group', 'country_id'],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid', example: '0196354c-c51f-7b79-b5d0-72245f52f001'),
        new OA\Property(property: 'name', type: 'string', example: 'emmanuel'),
        new OA\Property(property: 'gender', type: 'string', example: 'male'),
        new OA\Property(property: 'age', type: 'integer', example: 25),
        new OA\Property(property: 'age_group', type: 'string', example: 'adult'),
        new OA\Property(property: 'country_id', type: 'string', example: 'NG'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'SuccessResponse',
    required: ['status', 'data'],
    properties: [
        new OA\Property(property: 'status', type: 'string', example: 'success'),
        new OA\Property(property: 'data', type: 'object', description: 'Operation payload.'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'ProfileResponse',
    required: ['status', 'data'],
    properties: [
        new OA\Property(property: 'status', type: 'string', example: 'success'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Profile'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'ExistingProfileResponse',
    required: ['status', 'message', 'data'],
    properties: [
        new OA\Property(property: 'status', type: 'string', example: 'success'),
        new OA\Property(property: 'message', type: 'string', example: 'Profile already exists'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Profile'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'ProfilesListResponse',
    required: ['status', 'count', 'data'],
    properties: [
        new OA\Property(property: 'status', type: 'string', example: 'success'),
        new OA\Property(property: 'count', type: 'integer', example: 2),
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ProfileListItem')),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'ErrorResponse',
    required: ['status', 'message'],
    properties: [
        new OA\Property(property: 'status', type: 'string', example: 'error'),
        new OA\Property(property: 'message', type: 'string', example: 'Profile not found.'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'HealthResponse',
    required: ['status', 'message', 'service', 'version', 'timestamp'],
    properties: [
        new OA\Property(property: 'status', type: 'string', example: 'success'),
        new OA\Property(property: 'message', type: 'string', example: 'API is running'),
        new OA\Property(property: 'service', type: 'string', example: 'stage-1-profile-classification-api'),
        new OA\Property(property: 'version', type: 'string', example: '1.0.0'),
        new OA\Property(property: 'timestamp', type: 'string', format: 'date-time', example: '2026-04-17T09:30:00Z'),
    ],
    type: 'object'
)]
class ProfileSchemas {}
