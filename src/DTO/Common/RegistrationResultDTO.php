<?php

namespace Extrareality\DTO\Common;

use JsonSerializable;

class RegistrationResultDTO implements JsonSerializable
{
    public bool $success;
    public ?string $message = null;
    public ?string $payUrl = null;

    public function __construct(array $data = [])
    {
        $this->success = (bool) ($data['success'] ?? true);
        $this->message = $data['message'] ?? null;
        $this->payUrl = $data['payUrl'] ?? null;
    }

    public function jsonSerialize(): array
    {
        $data = [
            'success' => $this->success,
        ];

        if (!empty($this->message)) {
            $data['message'] = $this->message;
        }

        if (!empty($this->payUrl)) {
            $data['payUrl'] = $this->payUrl;
        }

        return $data;
    }
}