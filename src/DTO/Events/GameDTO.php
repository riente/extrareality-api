<?php

namespace Extrareality\DTO\Events;

use JsonSerializable;

class GameDTO implements JsonSerializable
{
    public int $id;
    public string $brand;
    public string $name;
    public ?string $img = null;
    public ?string $description = null;

    public function __construct(array $data = [])
    {
        $this->id = (int) ($data['id'] ?? 0);
        $this->brand = $data['brand'] ?? '';
        $this->name = $data['name'] ?? '';
        $this->img = $data['img'] ?? null;
        $this->description = $data['description'] ?? null;
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'id' => $this->id,
            'brand' => $this->brand,
            'name' => $this->name,
            'img' => $this->img,
            'description' => $this->description,
        ]);
    }
}