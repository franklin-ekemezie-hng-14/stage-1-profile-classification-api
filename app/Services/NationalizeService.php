<?php

namespace App\Services;

use App\DTOs\NationalizeData;
use App\Exceptions\ExternalApiDataException;
use App\Exceptions\ExternalApiRequestException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class NationalizeService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * @throws ExternalApiRequestException
     * @throws ExternalApiDataException
     */
    public function nationalize(string $name): NationalizeData
    {
        $apiEndpoint = config()->string('services.nationalize.url');
        $apiName = config()->string('services.nationalize.name');


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

        if (empty($data['country']) || $data['count'] === 0) {

            throw new ExternalApiDataException(
                $apiName, "Could not nationalise [$name]: No countries found for name."
            );

        }

        $possibleCountry = $this->determinePossibleCountry($data['country']);

        return NationalizeData::from($name, $possibleCountry);

    }


    /**
     * @param array<int, array{country_id: string, probability: float}> $countries
     * @return array{country_id: string, probability: float}
     */
    public function determinePossibleCountry(array $countries): array
    {
        /** @var array{country_id: string, probability: float} $possibleCountry */
        $possibleCountry = $countries[0];
        foreach ($countries as $country) {

            $countryProbability = (float) $country['probability'];

            if ($countryProbability > $possibleCountry['probability']) {
                $possibleCountry = $country;
            }

        }

        return $possibleCountry;
    }
}
