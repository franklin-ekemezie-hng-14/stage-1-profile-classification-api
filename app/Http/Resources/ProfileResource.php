<?php

namespace App\Http\Resources;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;


class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        /** @var Carbon $createdAt */
        $createdAt = $this['created_at'];

        return [
            'id'                    => $this['uuid'],
            'name'                  => $this['name'],
            'gender'                => $this['gender'],
            'gender_probability'    => $this['gender_probability'],
            'sample_size'           => $this['sample_size'],
            'age'                   => $this['age'],
            'age_group'             => $this['age_group'],
            'country_id'            => $this['country_id'],
            'country_probability'   => $this['country_probability'],
            'created_at'            => $createdAt->toISOString(),
        ];
    }
}
