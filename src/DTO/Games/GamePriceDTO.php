<?php

namespace Extrareality\DTO\Games;

use JsonSerializable;

class GamePriceDTO implements JsonSerializable
{
    public int|float $amount = 0;
    public string $currency = 'EUR';
    public ?string $description = null;

    public function __construct(array $data = [])
    {
        if (!empty($data['amount'])) {
            $this->amount = (float) $data['amount'];
        }

        if (!empty($data['currency'])) {
            $this->currency = (string) $data['currency'];
        }

        if (!empty($data['description'])) {
            $this->description = $data['description'];
        }
    }

    public function jsonSerialize(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'description' => $this->description,
        ];
    }
}