<?php

namespace App\Models;

use App\DTOs\ProfileData;
use App\Enums\AgeGroup;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $uuid
 */
#[Fillable(['name', 'gender', 'gender_probability', 'sample_size', 'age', 'age_group', 'country_id', 'country_probability'])]
class Profile extends Model
{
    //

    use HasUuids;

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function toProfileData(): ProfileData
    {
        return ProfileData::from($this->name)
            ->setId($this->uuid)
            ->setGender($this->gender)
            ->setGenderProbability($this->gender_probability)
            ->setSampleSize($this->sample_size)
            ->setAge($this->age)
            ->setAgeGroup($this->age_group)
            ->setCountryId($this->country_id)
            ->setCountryProbability($this->country_probability)
            ->setCreatedAt($this->created_at);
    }

    protected function casts(): array
    {
        return [
            'age_group'             => AgeGroup::class,
            'country_probability'   => 'float',
            'sample_size'           => 'integer',
            'age'                   => 'integer',
            'gender_probability'    => 'float',
        ];
    }

}

