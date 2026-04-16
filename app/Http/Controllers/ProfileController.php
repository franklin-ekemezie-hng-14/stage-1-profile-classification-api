<?php

namespace App\Http\Controllers;

use App\Actions\CreateProfileAction;
use App\DTOs\ProfileData;
use App\Exceptions\ExternalApiException;
use App\Http\Resources\CreateProfileResource;
use App\Models\Profile;
use App\Repositories\ProfileRepositoryInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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
            'status'    => 'success',
            'data'      => $data,
            'count'     => $data->count(),
        ];

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     * @throws ExternalApiException
     */
    public function store(Request $request)
    {
        //

        $name = $request->input('name');

        if (! $name) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Missing or empty name'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (! is_string($name)) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Name must be a string'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $profile = app(CreateProfileAction::class)->execute($name);

        if ($profile->isRetrieved()) {
            return response()->json([
                'status'    => 'success',
                'message'   => 'Profile already exists',
                'data'      => CreateProfileResource::make($profile),
            ], Response::HTTP_CREATED);
        }

        return response()->json([
            'status'    => 'success',
            'data'      => CreateProfileResource::make($profile),
        ], Response::HTTP_CREATED);

    }

    /**
     * Display the specified resource.
     */
    public function show(Profile $profile)
    {
        //

        return response()->json([
            'status'    => 'success',
            'data'      => $profile->toResource(),
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Profile $profile, ProfileRepositoryInterface $profiles)
    {
        //

        if (! $profiles->delete($profile->uuid)) {

            return response()->json([
                'status'    => 'error',
                'message'   => 'Failed to delete profile'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Profile deleted',
        ], Response::HTTP_NO_CONTENT);

    }
}
