<?php

namespace Extrareality\Enums;

enum RegistrationStatus: string
{
    case NEW = 'new';
    case CONFIRMED = 'confirmed';
    case RESERVE = 'reserve';
    case CANCELLED = 'cancelled';

    public static function castToEnum(null|string|RegistrationStatus $status): RegistrationStatus
    {
        if ($status instanceof RegistrationStatus) {
            return $status;
        }

        return match ($status) {
            'confirmed' => RegistrationStatus::CONFIRMED,
            'reserve' => RegistrationStatus::RESERVE,
            'cancelled' => RegistrationStatus::CANCELLED,
            default => RegistrationStatus::NEW,
        };
    }
}
