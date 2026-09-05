<?php

namespace Extrareality\DTO\EscapeRoom;

/**
 * Incoming booking request from ExtraReality.
 *
 * @see docs/APIv2.md#booking
 */
class BookingRequestDTO extends AbstractEscapeRoomRequestDTO
{
    public ?string $comment = null;
    public string $datetime = '';
    public ?string $email = null;
    public string $name = '';
    public string $phone = '';
    public int $playersNum = 0;
    public int|float $price = 0;
    public string $source = '';
    public int $uid = 0;
    public ?string $promoCode = null;

    public ?string $paymentMethod = null;
    public ?string $gameLanguage = null;
    public ?string $gameMode = null;
    public ?string $additionalServices = null;
    public int|float|null $servicesPrice = null;
    public int|float|null $servicesCommission = null;
    public ?string $invoiceData = null;

    /** @var array<string, mixed> Slot-specific fields such as our_time_id */
    public array $extra = [];

    public function __construct(array $data = [])
    {
        parent::__construct($data);

        $this->comment = isset($data['comment']) ? (string) $data['comment'] : null;
        $this->datetime = (string) ($data['datetime'] ?? '');
        $this->email = array_key_exists('email', $data) ? ($data['email'] !== null ? (string) $data['email'] : null) : null;
        $this->name = (string) ($data['name'] ?? '');
        $this->phone = (string) ($data['phone'] ?? '');
        $this->playersNum = (int) ($data['players_num'] ?? 0);
        $this->price = isset($data['price']) ? (float) $data['price'] : 0;
        $this->source = (string) ($data['source'] ?? '');
        $this->uid = (int) ($data['uid'] ?? 0);
        $this->promoCode = isset($data['promo_code']) ? (string) $data['promo_code'] : null;

        $this->paymentMethod = $data['payment_method'] ?? null;
        $this->gameLanguage = $data['game_language'] ?? null;
        $this->gameMode = $data['game_mode'] ?? null;
        $this->additionalServices = $data['additional_services'] ?? null;
        $this->servicesPrice = isset($data['services_price']) ? (float) $data['services_price'] : null;
        $this->servicesCommission = isset($data['services_commission']) ? (float) $data['services_commission'] : null;
        $this->invoiceData = $data['invoice_data'] ?? null;

        $this->extra = self::extractExtra($data, self::knownFields());
    }

    /** @return list<string> */
    private static function knownFields(): array
    {
        return [
            'comment', 'datetime', 'email', 'name', 'phone', 'players_num', 'price',
            'signature', 'source', 'uid', 'promo_code', 'payment_method', 'game_language',
            'game_mode', 'additional_services', 'services_price', 'services_commission',
            'invoice_data',
        ];
    }
}
