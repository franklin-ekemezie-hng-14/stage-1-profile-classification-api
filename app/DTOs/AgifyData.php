<?php

namespace App\DTOs;

use App\Enums\AgeGroup;

class AgifyData
{

    protected string $name;

    protected string $age;

    protected int $sampleSize;


    /**
     * Create a new class instance.
     */
    public function __construct(string $name, int $age)
    {
        //

        $this->name = $name;
        $this->age = $age;
    }

    public static function from(string $name, int $age): self
    {
        return new self($name, $age);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function getSampleSize(): int
    {
        return $this->sampleSize;
    }

    public function setSampleSize(int $sampleSize): self
    {
        $this->sampleSize = $sampleSize;
        return $this;
    }

    public function getAgeGroup(): AgeGroup
    {
        return AgeGroup::fromAge($this->age);
    }

    public function toArray(): array
    {
        return [
            'name'          => $this->name,
            'age'           => $this->age,
            'age_group'     => $this->getAgeGroup(),
            'sample_size'   => $this->sampleSize,
        ];
    }

}
