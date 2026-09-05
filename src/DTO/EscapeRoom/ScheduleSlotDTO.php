<?php

namespace Extrareality\DTO\EscapeRoom;

use JsonSerializable;

/**
 * A single schedule slot returned from the partner schedule endpoint.
 *
 * @see docs/APIv2.md#schedule
 */
class ScheduleSlotDTO implements JsonSerializable
{
    public string $date = '';
    public string $time = '';
    public bool $isFree = true;

    /** @var array<string|int, int|float> Condition or player count => price */
    public array $extraPrices = [];

    /** @var array<string, mixed> Custom fields echoed back on booking, e.g. our_time_id */
    public array $extra = [];

    public function __construct(array $data = [])
    {
        $this->date = (string) ($data['date'] ?? '');
        $this->time = (string) ($data['time'] ?? '');
        $this->isFree = (bool) ($data['is_free'] ?? true);

        if (isset($data['extraPrices']) && is_array($data['extraPrices'])) {
            $this->extraPrices = $data['extraPrices'];
        }

        $known = ['date', 'time', 'is_free', 'extraPrices'];
        foreach ($data as $key => $value) {
            if (!in_array($key, $known, true)) {
                $this->extra[$key] = $value;
            }
        }
    }

    public function jsonSerialize(): array
    {
        return array_merge(
            [
                'date' => $this->date,
                'time' => $this->time,
                'is_free' => $this->isFree,
                'extraPrices' => $this->extraPrices,
            ],
            $this->extra
        );
    }
}
