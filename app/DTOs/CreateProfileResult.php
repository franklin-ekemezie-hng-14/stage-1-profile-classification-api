<?php

namespace App\DTOs;

class CreateProfileResult
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected ProfileData $profile,
        protected bool        $isCreated,
    )
    {
        //
    }

    public function getProfile(): ProfileData
    {
        return $this->profile;
    }

    public function isCreated(): bool
    {
        return $this->isCreated;
    }

    public function isRetrieved(): bool
    {
        return ! $this->isCreated();
    }
}
