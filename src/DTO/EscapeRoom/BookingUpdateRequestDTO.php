<?php

namespace Extrareality\DTO\EscapeRoom;

/**
 * Incoming booking update from ExtraReality.
 *
 * @see docs/APIv2.md#update-booking
 */
class BookingUpdateRequestDTO extends AbstractEscapeRoomRequestDTO
{
    public int $uid = 0;
    public string $datetime = '';
    public int $questId = 0;

    public ?string $phone = null;
    public ?string $comment = null;
    public int|float|null $price = null;
    public ?int $playersNum = null;
    public ?bool $isPaid = null;

    public function __construct(array $data = [])
    {
        parent::__construct($data);

        $this->uid = (int) ($data['uid'] ?? 0);
        $this->datetime = (string) ($data['datetime'] ?? '');
        $this->questId = (int) ($data['quest_id'] ?? 0);

        if (array_key_exists('phone', $data)) {
            $this->phone = (string) $data['phone'];
        }

        if (array_key_exists('comment', $data)) {
            $this->comment = (string) $data['comment'];
        }

        if (array_key_exists('price', $data)) {
            $this->price = (float) $data['price'];
        }

        if (array_key_exists('players_num', $data)) {
            $this->playersNum = (int) $data['players_num'];
        }

        if (array_key_exists('is_paid', $data)) {
            $this->isPaid = filter_var($data['is_paid'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                ?? (bool) $data['is_paid'];
        }
    }
}
