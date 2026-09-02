<?php

namespace Extrareality\DTO\Forms;

use Extrareality\Enums\FormFieldType;
use JsonSerializable;

/**
 * The form we show to the user, and the endpoint we send the filled-in data to.
 *
 * Lives under registration.form of an event and directly under form of a game, which is why the
 * team and player limits sit here rather than on the registration object.
 *
 * @see https://github.com/riente/extrareality-api/blob/master/docs/FormObject.md
 */
class FormDTO implements JsonSerializable
{
    public ?string $openTime = null;

    /**
     * Null when the partner sent a form with nothing to submit to. Not worth aborting a whole
     * events list over, but such a form cannot be filled in either, so check it before using it
     */
    public ?FormEndpointDTO $endpoint = null;

    public ?int $maxTeams = null;
    public ?int $maxPlayers = null;

    /** @var array<FormFieldDTO> */
    public array $fields = [];

    public function __construct(array $data = [])
    {
        $this->openTime = $data['openTime'] ?? null;
        $this->endpoint = self::readEndpoint($data['endpoint'] ?? null);
        $this->maxTeams = isset($data['maxTeams']) ? (int) $data['maxTeams'] : null;
        $this->maxPlayers = isset($data['maxPlayers']) ? (int) $data['maxPlayers'] : null;
        $this->fields = self::readFields($data['fields'] ?? []);
    }

    public function jsonSerialize(): array
    {
        return [
            'openTime' => $this->openTime,
            'endpoint' => $this->endpoint,
            'maxTeams' => $this->maxTeams,
            'maxPlayers' => $this->maxPlayers,
            'fields' => $this->fields,
        ];
    }

    private static function readEndpoint(mixed $data): ?FormEndpointDTO
    {
        if (!is_array($data) || empty($data['url'])) {
            return null;
        }

        // fromArray() maps those raw strings onto the enums and picks up "idempotent" too, so the
        // mapping lives in one place instead of drifting between here and there
        return FormEndpointDTO::fromArray($data);
    }

    /**
     * @return array<FormFieldDTO>
     */
    private static function readFields(mixed $data): array
    {
        if (!is_array($data)) {
            return [];
        }

        $fields = [];

        foreach ($data as $field) {
            if (!is_array($field)) {
                continue;
            }

            $type = FormFieldType::tryFrom((string) ($field['type'] ?? ''));

            // A field of an unknown type cannot be rendered, and one without a name has nothing to
            // send the answer under. Skipping either beats guessing what the partner meant
            if ($type === null || empty($field['name'])) {
                continue;
            }

            $fields[] = new FormFieldDTO(
                type: $type,
                name: (string) $field['name'],
                title: (string) ($field['title'] ?? ''),
                required: (bool) ($field['required'] ?? false),
                description: $field['description'] ?? null,
                variants: $field['variants'] ?? null,
                value: self::readValue($field['value'] ?? null),
                max: self::readMax($field['max'] ?? null),
            );
        }

        return $fields;
    }

    /**
     * The value travels back to the partner in the registration body, so whatever scalar they sent
     * has to survive the trip. A bool reads as the 1/0 the docs show for a checkbox
     */
    private static function readValue(mixed $value): int|float|string|null
    {
        if (is_bool($value)) {
            return (int) $value;
        }

        return is_int($value) || is_float($value) || is_string($value) ? $value : null;
    }

    private static function readMax(mixed $max): int|float|null
    {
        if (!is_numeric($max)) {
            return null;
        }

        // A character count is an integer and a price cap may not be, so keep whichever was sent
        return $max == (int) $max ? (int) $max : (float) $max;
    }
}
