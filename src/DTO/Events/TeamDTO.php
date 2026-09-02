<?php

namespace Extrareality\DTO\Events;

use Extrareality\Enums\RegistrationStatus;
use JsonSerializable;

class TeamDTO implements JsonSerializable
{
    public int $id;
    public string $name;

    /**
     * @deprecated Addresses now come from the contacts endpoint, see ContactDTO. Still read here so
     *             that partners who have not migrated keep working.
     */
    public ?string $email = null;

    public int|string $players;
    public RegistrationStatus $status = RegistrationStatus::NEW;

    public function __construct(array $data = [])
    {
        $this->id = (int) ($data['id'] ?? 0);
        $this->name = $data['name'] ?? '';
        $this->email = $data['email'] ?? null;
        $this->players = $data['players'] ?? 1;
        $this->status = RegistrationStatus::castToEnum($data['status'] ?? null);
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