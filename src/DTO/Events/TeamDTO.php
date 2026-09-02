<?php

namespace Extrareality\DTO\Events;

use Extrareality\Enums\RegistrationStatus;
use JsonSerializable;

class TeamDTO implements JsonSerializable
{
    public int $id;
    public string $name;
    public int|string $players;
    public RegistrationStatus $status = RegistrationStatus::NEW;

    public function __construct(array $data = [])
    {
        $this->id = (int) ($data['id'] ?? 0);
        $this->name = $data['name'] ?? '';
        $this->players = $data['players'] ?? 1;
        $this->status = RegistrationStatus::castToEnum($data['status'] ?? null);
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'players' => $this->players,
            'status' => $this->status->value,
        ];
    }
}