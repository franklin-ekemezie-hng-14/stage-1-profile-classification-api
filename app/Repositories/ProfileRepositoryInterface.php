<?php

namespace App\Repositories;

use App\DTOs\ProfileData;
use Illuminate\Support\Collection;

interface ProfileRepositoryInterface
{
    //

    public function findByName(string $name): ?ProfileData;

    public function findById(string $id): ?ProfileData;

    /**
     * @param array $filters
     * @param callable|null $transformCallback
     * @return Collection<ProfileData | array | object>
     */
    public function getAll(array $filters = [], callable $transformCallback = null): Collection;

    public function create(array $data): ProfileData;

    public function delete(string $id): bool;
}
