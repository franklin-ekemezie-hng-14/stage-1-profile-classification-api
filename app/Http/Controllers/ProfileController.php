<?php

namespace App\Http\Controllers;

use App\Actions\CreateProfileAction;
use App\DTOs\ProfileData;
use App\Http\Resources\CreateProfileResource;
use App\Models\Profile;
use App\Repositories\ProfileRepositoryInterface;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends Controller
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
    public function index(Request $request, ProfileRepositoryInterface $profiles)
    {
        //

        $filters = collect($request->query())
            ->only('gender', 'country_id', 'age_group')
            ->toArray();

        $transformCallback = function (ProfileData $profile) {
            $data = $profile->toArray();
            $keys = ['id', 'name', 'gender', 'age', 'age_group', 'country_id'];

            return collect($data)->only($keys)->toArray();
        };

        $data = $profiles->getAll($filters, $transformCallback);

        return [
            'status' => 'success',
            'data' => $data,
            'count' => $data->count(),
        ];

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

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
    public function store(Request $request)
    {
        //

        $name = $request->input('name');

        if (! $name) {
            return response()->json([
                'status' => 'error',
                'message' => 'Missing or empty name',
            ], Response::HTTP_BAD_REQUEST);
        }

        if (! is_string($name)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Name must be a string',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $profile = app(CreateProfileAction::class)->execute($name);

        if ($profile->isRetrieved()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Profile already exists',
                'data' => CreateProfileResource::make($profile),
            ], Response::HTTP_CREATED);
        }

        return response()->json([
            'status' => 'success',
            'data' => CreateProfileResource::make($profile),
        ], Response::HTTP_CREATED);

    }

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
    public function show(Profile $profile)
    {
        //

        return response()->json([
            'status' => 'success',
            'data' => $profile->toResource(),
        ]);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Profile $profile)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Profile $profile)
    {
        //
    }

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
    public function destroy(Profile $profile, ProfileRepositoryInterface $profiles)
    {
        //

        if (! $profiles->delete($profile->uuid)) {

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete profile',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Profile deleted',
        ], Response::HTTP_NO_CONTENT);

    }
}
