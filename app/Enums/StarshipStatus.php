<?php

namespace App\Enums;

enum StarshipStatus: int
{
    // Idle, In Transit, Under Maintenance

    case IDLE = 1;
    case IN_TRANSIT = 2;
    case UNDER_MAINTENANCE = 3;


    public function label(): string
    {
        return match ($this) {
            self::IDLE => 'Idle',
            self::IN_TRANSIT => 'In Transit',
            self::UNDER_MAINTENANCE => 'Under Maintenance',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::IDLE => 'green',
            self::IN_TRANSIT => 'yellow',
            self::UNDER_MAINTENANCE => 'red',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::IDLE => 'heroicon-s-check-circle',
            self::IN_TRANSIT => 'heroicon-s-truck',
            self::UNDER_MAINTENANCE => 'heroicon-s-wrench',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::IDLE => 'The starship is currently idle.',
            self::IN_TRANSIT => 'The starship is currently in transit.',
            self::UNDER_MAINTENANCE => 'The starship is currently under maintenance.',
        };
    }



}
