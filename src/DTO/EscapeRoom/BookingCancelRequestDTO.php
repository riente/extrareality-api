<?php

namespace Extrareality\DTO\EscapeRoom;

/**
 * Incoming booking cancellation from ExtraReality.
 *
 * @see docs/APIv2.md#cancel-booking
 */
class BookingCancelRequestDTO extends AbstractEscapeRoomRequestDTO
{
    public string $datetime = '';
    public string $phone = '';
    public int $questId = 0;
    public int $uid = 0;

    public function __construct(array $data = [])
    {
        parent::__construct($data);

        $this->datetime = (string) ($data['datetime'] ?? '');
        $this->phone = (string) ($data['phone'] ?? '');
        $this->questId = (int) ($data['quest_id'] ?? 0);
        $this->uid = (int) ($data['uid'] ?? 0);
    }
}
