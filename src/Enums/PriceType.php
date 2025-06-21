<?php

namespace Extrareality\Enums;

enum PriceType: string
{
    case PLAYER = 'player';
    case TEAM = 'team';

    public static function castToEnum(string|PriceType $type): PriceType
    {
        if ($type instanceof PriceType) {
            return $type;
        }

        return match ($type) {
            'player' => PriceType::PLAYER,
            default => PriceType::TEAM,
        };
    }
}
