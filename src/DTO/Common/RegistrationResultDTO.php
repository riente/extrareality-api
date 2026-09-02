<?php

namespace Extrareality\DTO\Common;

use JsonSerializable;

class RegistrationResultDTO implements JsonSerializable
{
    public bool $success;
    public ?string $message = null;
    public ?string $payUrl = null;

    /** The registration's id in the partner's system, null if they do not return one yet */
    public ?int $registrationId = null;

    public function __construct(array $data = [])
    {
        $this->success = (bool) ($data['success'] ?? true);
        $this->message = $data['message'] ?? null;
        $this->payUrl = $data['payUrl'] ?? null;
        $this->registrationId = isset($data['registrationId']) ? (int) $data['registrationId'] : null;
    }

    public function jsonSerialize(): array
    {
        $data = [
            'success' => $this->success,
        ];

        if (!is_null($this->registrationId)) {
            $data['registrationId'] = $this->registrationId;
        }

        if (!empty($this->message)) {
            $data['message'] = $this->message;
        }

        if (!empty($this->payUrl)) {
            $data['payUrl'] = $this->payUrl;
        }

        return $data;
    }
}