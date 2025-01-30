<?php

namespace App\Enums;

enum SpaceWeatherCondition: string
{
    case CLEAR = 'clear';
    case METEOR_STORM = 'meteor_storm';
    case SOLAR_FLARE = 'solar_flare';
    case COSMIC_RADIATION = 'cosmic_radiation';
    case ION_STORM = 'ion_storm';

    public function getDelay(): int
    {
        return match($this) {
            self::CLEAR => 0,
            self::METEOR_STORM => 3600, // 1 hour delay
            self::SOLAR_FLARE => 7200, // 2 hours delay
            self::COSMIC_RADIATION => 1800, // 30 minutes delay
            self::ION_STORM => 5400, // 1.5 hours delay
        };
    }

    public function getDescription(): string
    {
        return match($this) {
            self::CLEAR => 'All clear for space travel',
            self::METEOR_STORM => 'Dense meteor activity causing route delays',
            self::SOLAR_FLARE => 'Solar flare activity disrupting navigation systems',
            self::COSMIC_RADIATION => 'Elevated cosmic radiation levels',
            self::ION_STORM => 'Ion storm interfering with ship systems',
        };
    }
}