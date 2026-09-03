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

    /** @var array<TeamDTO> */
    public array $teams = [];

    public ?FormDTO $form = null;

    public function __construct(array $data = [])
    {
        $this->contactsUrl = $data['contactsUrl'] ?? null;
        $this->form = isset($data['form']) && is_array($data['form']) ? new FormDTO($data['form']) : null;
        $this->teams = self::readTeams($data['teams'] ?? []);
    }

    public function jsonSerialize(): array
    {
        return [
            'contactsUrl' => $this->contactsUrl,
            'teams' => $this->teams,
            'form' => $this->form,
        ];
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
