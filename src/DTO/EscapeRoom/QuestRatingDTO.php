<?php

namespace Extrareality\DTO\EscapeRoom;

use JsonSerializable;

/**
 * @see docs/APIv2.md#fetch-quest-rating
 */
class QuestRatingDTO implements JsonSerializable
{
    public int $questId = 0;
    public float $rating = 0.0;

    public function __construct(array $data = [])
    {
        $this->questId = (int) ($data['questId'] ?? $data['quest_id'] ?? 0);
        $this->rating = (float) ($data['rating'] ?? 0);
    }

    public function jsonSerialize(): array
    {
        return [
            'questId' => $this->questId,
            'rating' => $this->rating,
        ];
    }
}
