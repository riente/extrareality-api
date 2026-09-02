<?php

namespace Extrareality\DTO\Events;

use JsonSerializable;

/**
 * Where an offline event takes place.
 *
 * @see https://github.com/riente/extrareality-api/blob/master/docs/EventsAPIv1.md#properties-description
 */
class CoordinatesDTO implements JsonSerializable
{
    public function __construct(public float $lat, public float $long)
    {
    }

    /**
     * The docs describe lat and long as floats, but partners send them as strings often enough that
     * the example in the docs itself shows `{ lat: "", long: "" }`. A pair we cannot read as numbers
     * is better reported as "no coordinates" than as a point in the ocean off Africa.
     */
    public static function tryFromArray(mixed $data): ?CoordinatesDTO
    {
        if (!is_array($data) || !isset($data['lat'], $data['long'])) {
            return null;
        }

        if (!is_numeric($data['lat']) || !is_numeric($data['long'])) {
            return null;
        }

        return new CoordinatesDTO((float) $data['lat'], (float) $data['long']);
    }

    public function jsonSerialize(): array
    {
        return [
            'lat' => $this->lat,
            'long' => $this->long,
        ];
    }
}
