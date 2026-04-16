<?php

namespace App\Services;

use App\DTOs\GenderizeData;
use App\Exceptions\ExternalApiDataException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class GenderizeService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }


    /**
     * @throws ExternalApiDataException
     */
    public function genderize(string $name): GenderizeData
    {

        $apiEndpoint = config()->string('services.genderize.url');
        $apiName = config()->string('services.genderize.name');

        try {

            $response = Http::timeout(3)
                ->get($apiEndpoint, ['name' => $name])
                ->throw();

            $data = $response->json();

        } catch (ConnectionException|RequestException $e) {

            throw new ExternalApiDataException(
                $apiName, $e->getMessage(), $e->getCode()
            );

        }

        if ($data['gender'] === null || $data['count'] === 0) {

            throw new ExternalApiDataException($apiName);
        }

        return GenderizeData::from($name, $data['gender'])
            ->setProbability($data['probability'])
            ->setSampleSize($data['count']);
    }
}
