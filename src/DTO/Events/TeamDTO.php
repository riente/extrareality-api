<?php

namespace Extrareality\DTO\Events;

use Extrareality\Enums\RegistrationStatus;
use JsonSerializable;

class TeamDTO implements JsonSerializable
{
    public int $id;
    public string $name;
    public ?string $email = null;
    public int|string $players;
    public RegistrationStatus $status = RegistrationStatus::NEW;

    public function __construct(array $data = [])
    {
        $this->id = (int) ($data['id'] ?? 0);
        $this->name = $data['name'] ?? '';
        $this->email = $data['email'] ?? null;
        $this->players = $data['players'] ?? 1;

        if ($data['status'] instanceof RegistrationStatus) {
            $this->status = $data['status'];
        } elseif (is_string($data['status'])) {
            $this->status = match ($data['status']) {
                'confirmed' => RegistrationStatus::CONFIRMED,
                'reserve' => RegistrationStatus::RESERVE,
                'cancelled' => RegistrationStatus::CANCELLED,
                default => RegistrationStatus::NEW,
            };
        }
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'players' => $this->players,
            'status' => $this->status->value,
        ];
    }
}