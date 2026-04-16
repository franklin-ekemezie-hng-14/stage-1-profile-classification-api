<?php

namespace App\Repositories;

use App\DTOs\ProfileData;
use App\Models\Profile;
use Illuminate\Support\Collection;

class EloquentProfileRepository implements ProfileRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }


    public function findByName(string $name): ?ProfileData
    {
        return Profile::query()
            ->where('name', $name)
            ->first()
            ?->toProfileData();
    }

    public function findById(string $id): ?ProfileData
    {
        return Profile::query()
            ->where('uuid', $id)
            ->first()
            ?->toProfileData();
    }

    public function getAll(array $filters = [], callable|null $transformCallback=null): Collection
    {
        $query = Profile::query();

        if (! empty($filters['gender']) && is_string($filters['gender'])) {
            $query->whereRaw('LOWER(gender) = ?', strtolower($filters['gender']));
        }

        if (! empty($filters['country_id']) && is_string($filters['country_id'])) {
            $query->whereRaw('LOWER(country_id) = ?', strtolower($filters['country_id']));
        }

        if (! empty($filters['age_group']) && is_string($filters['age_group'])) {
            $query->whereRaw('LOWER(age_group) = ?', strtolower($filters['age_group']));
        }

        return $query
            ->get()
            ->map(function (Profile $profile) use ($transformCallback) {
                $profile = $profile->toProfileData();
                if ($transformCallback) {
                    return $transformCallback($profile);
                }

                return $profile;
            })
            ->collect();
    }

    public function create(array $data): ProfileData
    {
        /** @var Profile $profile */
        $profile = Profile::query()->create($data);

        return $profile->toProfileData();
    }

    public function delete(string $id): bool
    {
        return !! (Profile::query()
            ->where('uuid', $id)
            ->delete());
    }
}
