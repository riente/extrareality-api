<?php

namespace Extrareality\Enums;

enum EventStatus: string
{
    case ACTIVE = 'active';
    case SOLDOUT = 'soldout';
    case CANCELLED = 'cancelled';

    public static function castToEnum(null|string|EventStatus $status): EventStatus
    {
        if ($status instanceof EventStatus) {
            return $status;
        }

        return match ($status) {
            'soldout' => EventStatus::SOLDOUT,
            'cancelled' => EventStatus::CANCELLED,
            default => EventStatus::ACTIVE,
        };
    }

    /**
     * Whether we should show the registration form and send leads for such an event
     */
    public function acceptsRegistrations(): bool
    {
        return $this === EventStatus::ACTIVE;
    }
}
