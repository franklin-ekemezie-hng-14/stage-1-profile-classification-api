<?php

namespace App\DTOs;

class GenderizeData
{

    protected string $name;

    protected string $gender;

    protected float $probability;

    protected int $sampleSize;

    /**
     * Create a new class instance.
     */
    public function __construct(
        string $name,
        string $gender,
    )
    {
        //

        $this->name = $name;
        $this->gender = $gender;
    }

    public static function from(string $name, string $gender): self
    {
        return new self($name, $gender);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    public function setProbability(float $value): self
    {
        $this->probability = $value;
        return $this;
    }

    public function getProbability(): float
    {
        return $this->probability;
    }

    public function setSampleSize(int $value): self
    {
        $this->sampleSize = $value;
        return $this;
    }

    public function getSampleSize(): int
    {
        return $this->sampleSize;
    }

    public function toArray(): array
    {
        return [
            'name'          => $this->name,
            'gender'        => $this->gender,
            'probability'   => $this->probability,
            'sample_size'   => $this->sampleSize,
        ];
    }


}
