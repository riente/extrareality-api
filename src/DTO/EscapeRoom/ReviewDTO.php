<?php

namespace Extrareality\DTO\EscapeRoom;

use JsonSerializable;

/**
 * @see docs/APIv2.md#fetch-review-list
 */
class ReviewDTO implements JsonSerializable
{
    public int $id = 0;
    public string $datetime = '';
    public string $name = '';
    public string $text = '';
    public float $rating = 0.0;

    public function __construct(array $data = [])
    {
        $this->id = (int) ($data['id'] ?? 0);
        $this->datetime = (string) ($data['datetime'] ?? '');
        $this->name = (string) ($data['name'] ?? '');
        $this->text = (string) ($data['text'] ?? '');
        $this->rating = (float) ($data['rating'] ?? 0);
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'datetime' => $this->datetime,
            'name' => $this->name,
            'text' => $this->text,
            'rating' => $this->rating,
        ];
    }
}
