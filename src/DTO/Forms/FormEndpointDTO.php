<?php

namespace Extrareality\DTO\Forms;

use Extrareality\Enums\EndpointFormat;
use Extrareality\Enums\HttpMethod;
use Extrareality\Exceptions\ExtrarealityException;

class FormEndpointDTO
{
    public function __construct(
        public string $url,
        public HttpMethod $method = HttpMethod::POST,
        public EndpointFormat $format = EndpointFormat::FORM,
        /** Whether a repeated idempotency_key is guaranteed not to create a second registration */
        public bool $idempotent = false,
    ) {}

    /**
     * @throws ExtrarealityException
     */
    public static function fromArray(array $data = []): FormEndpointDTO
    {
        if (!isset($data['url'])) {
            throw new ExtrarealityException('Missing required "url" parameter');
        }

        // Mapped field by field: these arrive from JSON as strings, and any extra key a partner
        // sends would make an argument unpacking here fail
        return new FormEndpointDTO(
            $data['url'],
            HttpMethod::tryFrom(strtoupper((string) ($data['method'] ?? ''))) ?? HttpMethod::POST,
            EndpointFormat::tryFrom(strtolower((string) ($data['format'] ?? ''))) ?? EndpointFormat::FORM,
            (bool) ($data['idempotent'] ?? false),
        );
    }
}
