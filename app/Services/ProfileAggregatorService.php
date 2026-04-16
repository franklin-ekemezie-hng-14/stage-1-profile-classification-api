<?php

namespace App\Services;

use App\Exceptions\ExternalApiException;

class ProfileAggregatorService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected GenderizeService   $genderiseService,
        protected AgifyService       $agifyService,
        protected NationalizeService $nationalizeService,
    )
    {
        //


    }


    /**
     * @throws ExternalApiException
     */
    public function build(string $name): array
    {

        $genderData = $this->genderiseService->genderize($name);

        $ageData = $this->agifyService->agify($name);

        $nationalityData = $this->nationalizeService->nationalize($name);


        return [
            'name'                  => $name,

            'gender'                => $genderData->getGender(),
            'gender_probability'    => $genderData->getProbability(),
            'sample_size'           => $ageData->getSampleSize(),

            'age'                   => $ageData->getAge(),
            'age_group'             => $ageData->getAgeGroup(),

            'country_id'            => $nationalityData->getCountryId(),
            'country_probability'   => $nationalityData->getCountryProbability(),
        ];

    }
}
