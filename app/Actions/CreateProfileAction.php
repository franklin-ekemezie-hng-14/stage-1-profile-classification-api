<?php

namespace App\Actions;

use App\DTOs\CreateProfileResult;
use App\Exceptions\ExternalApiException;
use App\Repositories\ProfileRepositoryInterface;
use App\Services\ProfileAggregatorService;

class CreateProfileAction
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected ProfileRepositoryInterface $profiles,
        protected ProfileAggregatorService $profileAggregator,
    )
    {
        //
    }


    /**
     * @throws ExternalApiException
     */
    public function execute(string $name): CreateProfileResult
    {

        $name = strtolower($name);

        if ($profile = $this->profiles->findByName($name)) {
            return new CreateProfileResult($profile, false);
        }

        $data = $this->profileAggregator->build($name);

        $profile = $this->profiles->create($data);

        return new CreateProfileResult($profile, true);

    }
}
