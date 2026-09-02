<?php

namespace Extrareality\DTO\Events;

use Extrareality\DTO\Common\AbstractApiDTO;
use Extrareality\Enums\EventStatus;
use Extrareality\Enums\EventType;

/**
 * A single event, as returned by both the events list and the single event endpoint.
 *
 * The list gives a shorter version of the same object: coordinates and registration are only
 * promised by the single event endpoint, so treat them as absent until the event has been opened.
 *
 * @see https://github.com/riente/extrareality-api/blob/master/docs/EventsAPIv1.md#single-event
 */
class EventDTO extends AbstractApiDTO
{
    public int $id;

    /** Falls back to offline when the partner sends no type, following EventType::castToEnum() */
    public EventType $type;

    public EventStatus $status = EventStatus::ACTIVE;

    /** A short note shown next to the event, e.g. "Sold out, join the waiting list" */
    public ?string $statusComment = null;

    public GameDTO $game;

    /** Where the full game data lives, see GamesAPIv1 */
    public ?string $gameUrl = null;

    public string $location = '';
    public ?CoordinatesDTO $coordinates = null;

    /**
     * ISO 8601 with a UTC offset, kept exactly as the partner sent it
     *
     * @see https://github.com/riente/extrareality-api/blob/master/docs/EventsAPIv1.md#dates-and-times
     */
    public string $time = '';

    public EventPriceDTO $price;
    public ?RegistrationDTO $registration = null;

    /** URL of the single event endpoint, where the full version of this object lives */
    public ?string $url = null;

    public function __construct(array $data = [])
    {
        $this->id = (int) ($data['id'] ?? 0);
        $this->type = EventType::castToEnum((string) ($data['type'] ?? ''));
        $this->status = EventStatus::castToEnum($data['status'] ?? null);
        $this->statusComment = $data['statusComment'] ?? null;

        [$this->game, $this->gameUrl] = self::readGame($data);

        $this->location = (string) ($data['location'] ?? '');
        $this->coordinates = CoordinatesDTO::tryFromArray($data['coordinates'] ?? null);
        $this->time = (string) ($data['time'] ?? '');
        $this->price = new EventPriceDTO(is_array($data['price'] ?? null) ? $data['price'] : []);
        $this->url = $data['url'] ?? null;

        $this->registration = isset($data['registration']) && is_array($data['registration'])
            ? new RegistrationDTO($data['registration'])
            : null;
    }

    /**
     * Whether we should show the form and send the partner leads for this event.
     *
     * A sold out or cancelled event keeps its registration object, so the status has to be checked
     * separately from the presence of a form
     */
    public function acceptsRegistrations(): bool
    {
        return $this->status->acceptsRegistrations() && $this->registration?->form?->endpoint !== null;
    }

    public function jsonSerialize(): array
    {
        $data = [
            'id' => $this->id,
            'type' => $this->type->value,
            'status' => $this->status->value,
            'game' => $this->game,
            'location' => $this->location,
            'time' => $this->time,
            'price' => $this->price,
        ];

        // Emitted only when the partner actually sent them, so that re-encoding an event from the
        // list does not invent a "registration": null it never claimed
        $optional = [
            'statusComment' => $this->statusComment,
            'gameUrl' => $this->gameUrl,
            'coordinates' => $this->coordinates,
            'registration' => $this->registration,
            'url' => $this->url,
        ];

        return array_merge($data, array_filter($optional, fn($value) => $value !== null));
    }

    public static function fromArray(array $data = []): EventDTO
    {
        return new EventDTO($data);
    }

    /**
     * @return array{GameDTO, string|null}
     */
    private static function readGame(array $data): array
    {
        $game = $data['game'] ?? null;

        return [new GameDTO(is_array($game) ? $game : []), $data['gameUrl'] ?? null];
    }
}
