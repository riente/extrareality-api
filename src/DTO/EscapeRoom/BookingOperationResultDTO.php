<?php

namespace Extrareality\DTO\EscapeRoom;

use JsonSerializable;

/**
 * JSON body for booking, update and cancel endpoints.
 *
 * HTTP status must always be 200, including failures.
 *
 * @see docs/APIv2.md#booking
 */
class BookingOperationResultDTO implements JsonSerializable
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $message = null,
    ) {
    }

    public static function success(): self
    {
        return new self(true);
    }

    public static function failure(string $message): self
    {
        return new self(false, $message);
    }

    public function jsonSerialize(): array
    {
        $data = ['success' => $this->success];

        if ($this->message !== null && $this->message !== '') {
            $data['message'] = $this->message;
        }

        return $data;
    }
}
