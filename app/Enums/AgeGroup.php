<?php

namespace App\Enums;

use Illuminate\Support\Number;

enum AgeGroup: string
{
    //

    case CHILD = 'child';

    case TEENAGER = 'teenager';

    case ADULT = 'adult';

    case SENIOR = 'senior';


    public static function fromAge(int $age): AgeGroup
    {
        return match (true) {

            ($age >= 0 && $age <= 12)   => self::CHILD,
            ($age >= 13 && $age <= 19)  => self::TEENAGER,
            ($age >= 20 && $age <= 59)  => self::ADULT,
            ($age >= 60)                => self::SENIOR,
        };
    }

    public static function values(): array
    {
        return array_map(fn (self $ageGroup) => $ageGroup->value, self::cases());
    }

}
