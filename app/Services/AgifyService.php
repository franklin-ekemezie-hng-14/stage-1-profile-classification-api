<?php

namespace App\Services;

use App\DTOs\AgifyData;
use App\Exceptions\ExternalApiDataException;
use App\Exceptions\ExternalApiException;
use App\Exceptions\ExternalApiRequestException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class AgifyService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }


    /**
     * @throws ExternalApiException
     */
    public function agify(string $name): AgifyData
    {

        $apiEndpoint = config()->string('services.agify.url');
        $apiName = config()->string('services.agify.name');

        try {

            $response = Http::timeout(3)
                ->get($apiEndpoint, ['name' => $name])
                ->throw();

            $data = $response->json();

        } catch (ConnectionException|RequestException $e) {

            throw new ExternalApiRequestException(
                $apiName, $e->getMessage(), $e->getCode(), $e
            );

        }

        if ($data['age'] === null || $data['count'] === 0) {

            throw new ExternalApiDataException(
                $apiName, "Could not agify [$name]: No age found for name."
            );

        }

        return AgifyData::from($name, $data['age'])
            ->setSampleSize($data['count']);

    }
}
