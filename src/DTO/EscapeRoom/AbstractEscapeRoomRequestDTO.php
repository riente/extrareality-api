<?php

namespace Extrareality\DTO\EscapeRoom;

abstract class AbstractEscapeRoomRequestDTO
{
    public string $datetime = '';
    public string $signature = '';

    public function __construct(array $data = [])
    {
        $this->datetime = (string) ($data['datetime'] ?? '');
        $this->signature = (string) ($data['signature'] ?? '');
    }

    /** @param list<string> $known */
    protected static function extractExtra(array $data, array $known): array
    {
        $extra = [];

        foreach ($data as $key => $value) {
            if (!in_array($key, $known, true)) {
                $extra[$key] = $value;
            }
        }

        return $extra;
    }
}
