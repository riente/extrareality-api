<?php

namespace Extrareality\DTO\Events;

use Extrareality\DTO\Common\AbstractApiDTO;

/**
 * A participant's email address, fetched from the endpoint given in registration.contactsUrl.
 *
 * Holds personal data: keep it out of logs and do not store it longer than the event needs it.
 *
 * @see https://github.com/riente/extrareality-api/blob/master/docs/EventsAPIv1.md#participant-contacts
 */
class ContactDTO extends AbstractApiDTO
{
    public int $registrationId;
    public string $email;
    public ?string $consentAt = null;

    public function __construct(array $data = [])
    {
        $this->registrationId = (int) ($data['registrationId'] ?? 0);
        $this->email = $data['email'] ?? '';
        $this->consentAt = $data['consentAt'] ?? null;
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'registrationId' => $this->registrationId,
            'email' => $this->email,
            'consentAt' => $this->consentAt,
        ]);
    }

    public static function fromArray(array $data = []): ContactDTO
    {
        return new ContactDTO($data);
    }

    /**
     * Deliberately hides the address, so that dumping or logging a contact does not leak it
     */
    public function __toString(): string
    {
        return sprintf('ContactDTO(registrationId: %d)', $this->registrationId);
    }
}
