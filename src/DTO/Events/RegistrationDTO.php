<?php

namespace Extrareality\DTO\Events;

use Extrareality\DTO\Forms\FormDTO;
use JsonSerializable;

/**
 * Who has signed up for an event, and how someone else still can.
 *
 * @see https://github.com/riente/extrareality-api/blob/master/docs/EventsAPIv1.md#single-event
 */
class RegistrationDTO implements JsonSerializable
{
    /**
     * Where the participants' email addresses can be fetched from. Personal data lives behind it,
     * so call it only when there is actually something to send them
     *
     * @see \Extrareality\Client\ExtrarealityEventsClient::getEventContacts()
     */
    public ?string $contactsUrl = null;

    /**
     * How much room is left: teams and people respectively, mirroring the form's capacities. Null
     * means the partner does not publish the number, which is not the same as zero
     *
     * @see https://github.com/riente/extrareality-api/blob/master/docs/EventsAPIv1.md#how-many-places-are-left
     */
    public ?int $teamsLeft = null;
    public ?int $placesLeft = null;

    /** @var array<TeamDTO> */
    public array $teams = [];

    public ?FormDTO $form = null;

    public function __construct(array $data = [])
    {
        $this->contactsUrl = $data['contactsUrl'] ?? null;
        $this->teamsLeft = isset($data['teamsLeft']) ? (int) $data['teamsLeft'] : null;
        $this->placesLeft = isset($data['placesLeft']) ? (int) $data['placesLeft'] : null;
        $this->form = isset($data['form']) && is_array($data['form']) ? new FormDTO($data['form']) : null;
        $this->teams = self::readTeams($data['teams'] ?? []);
    }

    public function jsonSerialize(): array
    {
        return [
            'contactsUrl' => $this->contactsUrl,
            'teamsLeft' => $this->teamsLeft,
            'placesLeft' => $this->placesLeft,
            'teams' => $this->teams,
            'form' => $this->form,
        ];
    }

    /**
     * Whether the event has publicly run out of room. False when the partner publishes no numbers,
     * so check the status too rather than treating this as "there is space"
     */
    public function isFull(): bool
    {
        return $this->teamsLeft === 0 || $this->placesLeft === 0;
    }

    /**
     * @return array<TeamDTO>
     */
    private static function readTeams(mixed $data): array
    {
        if (!is_array($data)) {
            return [];
        }

        return array_map(
            fn(array $team) => new TeamDTO($team),
            array_values(array_filter($data, 'is_array'))
        );
    }
}
