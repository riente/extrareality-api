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
    ) {}

    /**
     * @throws ExtrarealityException
     */
    public static function fromArray(array $data = []): FormEndpointDTO
    {
        if (!isset($data['url'])) {
            throw new ExtrarealityException('Missing required "url" parameter');
        }

        return new FormEndpointDTO(...$data);
    }
}
