<?php

namespace Extrareality\Client;

use Extrareality\DTO\Common\RegistrationResultDTO;
use Extrareality\DTO\Events\EventDTO;
use Extrareality\DTO\Events\GameDTO;
use Extrareality\DTO\Forms\FormEndpointDTO;
use Extrareality\Enums\EndpointFormat;
use Extrareality\Enums\HttpMethod;
use Extrareality\Exceptions\ExtrarealityException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;

/**
 * @link https://docs.guzzlephp.org/en/stable/quickstart.html
 */
class ExtrarealityEventsClient
{
    private Client $client;

    /**
     * @param string $source Identifier of your site, for example, requests from us will use "extrareality"
     * @param string $secret The secret key that both parties know
     */
    public function __construct(private readonly string $source, private readonly string $secret = '')
    {
        $this->client = new Client([
            // You can set any number of default request options.
            'timeout'  => 2.0,
        ]);
    }

    /**
     * @throws ExtrarealityException
     * @throws GuzzleException
     */
    public function getEventsList(string $eventsListUrl): array
    {
        $request = $this->prepareRequest(HttpMethod::GET, $eventsListUrl);
        $data = $this->sendRequest($request);

        return array_map(function ($item) {
            return new EventDTO($item);
        }, $data);
    }

    /**
     * @throws ExtrarealityException
     * @throws GuzzleException
     */
    public function getSingleEvent(string $eventUrl): EventDTO
    {
        $request = $this->prepareRequest(HttpMethod::GET, $eventUrl);
        $data = $this->sendRequest($request);

        return new EventDTO($data);
    }

    /**
     * @throws GuzzleException
     * @throws ExtrarealityException
     */
    public function getSingleGame(string $gameUrl): GameDTO
    {
        $request = $this->prepareRequest(HttpMethod::GET, $gameUrl);
        $data = $this->sendRequest($request);

        return new GameDTO($data);
    }

    /**
     * @throws ExtrarealityException
     * @throws GuzzleException
     */
    public function sendRegistration(FormEndpointDTO $endpoint, array $data = []): RegistrationResultDTO
    {
        $request = $this->prepareRequest($endpoint->method, $endpoint->url, $data, $endpoint->format);
        $data = $this->sendRequest($request);

        return new RegistrationResultDTO($data);
    }

    /**
     * @throws ExtrarealityException|GuzzleException
     */
    private function sendRequest(Request $request): array
    {
        $response = $this->client->send($request);

        if ($response->getStatusCode() !== 200) {
            throw new ExtrarealityException('Response code is not 200');
        }

        $content = $response->getBody();

        $data = json_decode($content, true);
        if (is_null($data)) {
            throw new ExtrarealityException('Response is not valid JSON');
        }

        return $data;
    }

    private function getSignature(?string $datetime = null): string
    {
        if (empty($datetime)) {
            $datetime = date('Y-m-d H:i:s');
        }

        return md5($this->source . $datetime . $this->secret);
    }

    private function prepareRequest(HttpMethod $method,
                                    string $url,
                                    array $data = [],
                                    EndpointFormat $format = EndpointFormat::FORM
    ): Request {
        if (!isset($data['datetime'])) {
            $data['datetime'] = date('Y-m-d H:i:s');
        }

        $signature = $this->getSignature($data['datetime']);

        $data['signature'] = $signature;
        $data['source'] = $this->source;

        $headers = [
            'X-Signature' => $signature,
            'X-Source' => $this->source,
        ];

        if ($method === HttpMethod::GET) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($data);
            $body = null;
        } else {
            $body = match ($format) {
                EndpointFormat::JSON => json_encode($data),
                default => http_build_query($data),
            };
        }

        return new Request($method->value, $url, $headers, $body);
    }
}