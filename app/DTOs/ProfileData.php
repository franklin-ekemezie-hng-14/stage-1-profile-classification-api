<?php

namespace App\DTOs;

use App\Enums\AgeGroup;
use Illuminate\Support\Carbon;

class ProfileData
{

    protected string $id;

    protected string $name;

    protected string $gender;

    protected float $genderProbability;

    protected int $sampleSize;

    protected int $age;

    protected AgeGroup $ageGroup;

    protected string $countryId;

    protected float $countryProbability;

    protected Carbon $createdAt;

    /**
     * Create a new class instance.
     */
    public function __construct(string $name)
    {
        //

        $this->name = $name;

    }

    public static function from(string $name): self
    {
        return new self($name);
    }

    public function getId(): string
    {
        return $this->id;
    }


    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }


    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setGender(string $gender): self
    {
        $this->gender = $gender;
        return $this;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    public function setGenderProbability(float $genderProbability): self
    {
        $this->genderProbability = $genderProbability;
        return $this;
    }

    public function getGenderProbability(): float
    {
        return $this->genderProbability;
    }

    public function setSampleSize(int $sampleSize): self
    {
        $this->sampleSize = $sampleSize;
        return $this;
    }

    public function getSampleSize(): int
    {
        return $this->sampleSize;
    }


    public function setAge(int $age): self
    {
        $this->age = $age;
        return $this;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function setAgeGroup(AgeGroup $ageGroup): self
    {
        $this->ageGroup = $ageGroup;
        return $this;
    }

    public function getAgeGroup(): AgeGroup
    {
        return $this->ageGroup;
    }

    public function setCountryId(string $countryId): self
    {
        $this->countryId = $countryId;
        return $this;
    }

    public function getCountryId(): string
    {
        return $this->countryId;
    }


    public function setCountryProbability(float $countryProbability): self
    {
        $this->countryProbability = $countryProbability;
        return $this;
    }

    public function getCountryProbability(): float
    {
        return $this->countryProbability;
    }

    public function setCreatedAt(Carbon $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getCreatedAt(): Carbon
    {
        return $this->createdAt;
    }


    public function toArray(): array
    {
        return [
            'id'                    => $this->id,
            'name'                  => $this->name,
            'gender'                => $this->gender,
            'gender_probability'    => $this->genderProbability,
            'sample_size'           => $this->sampleSize,
            'age'                   => $this->age,
            'age_group'             => $this->ageGroup->value,
            'country_id'            => $this->countryId,
            'country_probability'   => $this->countryProbability,
            'created_at'            => $this->createdAt,
        ];
    }
}
