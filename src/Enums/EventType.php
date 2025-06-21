<?php

namespace Extrareality\Enums;

enum EventType: string
{
    case ONLINE = 'online';
    case OFFLINE = 'offline';

    public static function castToEnum(string|EventType $type): EventType
    {
        if ($type instanceof EventType) {
            return $type;
        }

        return match ($type) {
            'online' => EventType::ONLINE,
            default => EventType::OFFLINE,
        };
    }
}
