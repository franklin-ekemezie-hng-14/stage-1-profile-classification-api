<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Stage 1 Profile Classification API',
    version: '1.0.0',
    description: 'API for creating, storing, retrieving, filtering, and deleting classified name profiles using Genderize, Agify, and Nationalize.'
)]
#[OA\Server(
    url: '/',
    description: 'Application server'
)]
#[OA\Tag(
    name: 'Health',
    description: 'Service health and status endpoints'
)]
#[OA\Tag(
    name: 'Profiles',
    description: 'Profile creation, retrieval, filtering, and deletion endpoints'
)]
class OpenApiSpec {}
