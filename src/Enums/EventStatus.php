<?php

namespace Extrareality\Enums;

enum EventStatus: string
{
    case ACTIVE = 'active';

    /** The main list is full, but the form stays open and new teams join the waiting list */
    case RESERVE = 'reserve';

    /** No more registrations at all. Rare: RESERVE keeps the form open instead */
    case SOLDOUT = 'soldout';

    public static function castToEnum(null|string|EventStatus $status): EventStatus
    {
        if ($status instanceof EventStatus) {
            return $status;
        }

        return match ($status) {
            'reserve' => EventStatus::RESERVE,
            'soldout' => EventStatus::SOLDOUT,
            default => EventStatus::ACTIVE,
        };
    }

    /**
     * Whether we should show the registration form and send leads for such an event.
     *
     * RESERVE still takes registrations — they simply join the waiting list
     */
    public function acceptsRegistrations(): bool
    {
        return $this !== EventStatus::SOLDOUT;
    }

    /**
     * Whether the person signing up has to be told they are joining the waiting list
     */
    public function isWaitingList(): bool
    {
        return $this === EventStatus::RESERVE;
    }
}
