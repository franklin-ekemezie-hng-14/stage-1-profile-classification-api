<?php

namespace App\DTOs;

class NationalizeData
{

    /** @var string  */
    protected string $name;

    /** @var array{country_id: string, probability: float}  */
    protected array $country;



    /**
     * Create a new class instance.
     * @param string $name
     * @param array{country_id: string, probability: float} $country
     */
    public function __construct(string $name, array $country)
    {
        //

        $this->name = $name;
        $this->country = $country;
    }

    public static function from(string $name, array $country): self
    {
        return new self($name, $country);
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array{country_id: string, probability: float}
     */
    public function getCountry(): array
    {
        return $this->country;
    }

    public function getCountryId(): string
    {
        return $this->country['country_id'];
    }


    public function getCountryProbability(): float
    {
        return $this->country['probability'];
    }
}
