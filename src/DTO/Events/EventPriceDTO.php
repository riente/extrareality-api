<?php

namespace Extrareality\DTO\Events;

use Extrareality\Enums\PriceType;
use JsonSerializable;

class EventPriceDTO implements JsonSerializable
{
    public int|float $amount = 0;
    public string $currency = 'EUR';
    public PriceType $per = PriceType::TEAM;

    public function __construct(array $data = [])
    {
        if (!empty($data['amount'])) {
            $this->amount = (float) $data['amount'];
        }

        if (!empty($data['currency'])) {
            $this->currency = (string) $data['currency'];
        }

        if (!empty($data['per'])) {
            $this->per = PriceType::castToEnum($data['per']);
        }
    }

    public function jsonSerialize(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'per' => $this->per->value,
        ];
    }
}