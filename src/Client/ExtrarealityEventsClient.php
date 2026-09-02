<?php

namespace Extrareality\Client;

use Extrareality\DTO\Common\RegistrationResultDTO;
use Extrareality\DTO\Events\ContactDTO;
use Extrareality\DTO\Events\EventDTO;
use Extrareality\DTO\Events\GameDTO;
use Extrareality\DTO\Forms\FormEndpointDTO;
use Extrareality\Enums\EndpointFormat;
use Extrareality\Enums\HttpMethod;
use DateTimeImmutable;
use DateTimeZone;
use Extrareality\Exceptions\ExtrarealityException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

/**
 * @link https://docs.guzzlephp.org/en/stable/quickstart.html
 */
class ExtrarealityEventsClient
{
    private const MAX_RETRIES = 2;
    private const MAX_RETRY_DELAY = 30;

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
            // We read the status code ourselves, see parseResponse(): a 403 or a 422 can still
            // carry a body we need, so an exception on every 4xx would throw it away
            'http_errors' => false,
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
     * Fetches the participants' email addresses from the URL given in registration.contactsUrl.
     *
     * Returns personal data, so call it only when there is something to send, keep the result out
     * of your logs and drop it once the emails are out.
     *
     * @return ContactDTO[]
     * @throws ExtrarealityException
     * @throws GuzzleException
     * @see https://github.com/riente/extrareality-api/blob/master/docs/EventsAPIv1.md#participant-contacts
     */
    public function getEventContacts(string $contactsUrl): array
    {
        $request = $this->prepareRequest(HttpMethod::GET, $contactsUrl);
        $data = $this->sendRequest($request, acceptBusinessErrors: true);

        // A refusal comes back as 403 with {"success": false}, which parseResponse() hands over
        // rather than throwing, so it must not be mistaken here for "nobody registered"
        if (isset($data['success']) && !$data['success']) {
            throw new ExtrarealityException(
                'Contacts request refused: ' . ($data['message'] ?? 'no reason given')
            );
        }

        if (!isset($data['contacts']) || !is_array($data['contacts'])) {
            throw new ExtrarealityException('Contacts response has no "contacts" array');
        }

        return array_map(function ($item) {
            return new ContactDTO($item);
        }, $data['contacts']);
    }

    /**
     * @param string|null $idempotencyKey Pass the key of an earlier attempt to repeat it safely.
     *                                    A fresh one is generated for a new registration.
     * @throws ExtrarealityException
     * @throws GuzzleException
     * @see https://github.com/riente/extrareality-api/blob/master/docs/FormObject.md#sending-the-same-registration-twice
     */
    public function sendRegistration(
        FormEndpointDTO $endpoint,
        array $data = [],
        ?string $idempotencyKey = null
    ): RegistrationResultDTO {
        // Travels in the body, so the signature covers it and a repeat is recognisable by its key
        $data['idempotency_key'] = $idempotencyKey ?? $this->generateIdempotencyKey();

        $request = $this->prepareRequest($endpoint->method, $endpoint->url, $data, $endpoint->format);

        // Repeating a registration is only safe once the endpoint promises to deduplicate by key
        $result = $this->sendRequest($request, acceptBusinessErrors: true, idempotent: $endpoint->idempotent);

        return new RegistrationResultDTO($result);
    }

    private function generateIdempotencyKey(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }

    /**
     * @param bool $acceptBusinessErrors Whether a 4xx carrying a {"success": false} body is an
     *                                   answer the caller wants to read rather than a failure
     * @param bool $idempotent Whether repeating this request cannot create a second record
     * @throws ExtrarealityException|GuzzleException
     * @see https://github.com/riente/extrareality-api/blob/master/docs/Responses.md
     */
    private function sendRequest(Request $request, bool $acceptBusinessErrors = false, bool $idempotent = false): array
    {
        // A lost response is indistinguishable from a lost request, so repeating is only safe when
        // the request changes nothing (GET) or the far side deduplicates it for us
        $canRetry = $request->getMethod() === HttpMethod::GET->value || $idempotent;

        $attempt = 0;
        $response = null;

        while (true) {
            try {
                $response = $this->client->send($request);
            } catch (GuzzleException $e) {
                // Out of attempts: rethrow the original error rather than a vague "no response",
                // because the connection error is what someone debugging this needs to see
                if (!$canRetry || $attempt >= self::MAX_RETRIES) {
                    throw $e;
                }

                $response = null;
            }

            if ($response !== null && !$this->isRetryable($response->getStatusCode())) {
                break;
            }

            if (!$canRetry || $attempt >= self::MAX_RETRIES) {
                break;
            }

            sleep($this->getRetryDelay($response, $attempt));
            $attempt++;
        }

        return $this->parseResponse($request, $response, $acceptBusinessErrors);
    }

    /**
     * "Come back later" and "we are broken right now" are worth another attempt, a refusal is not
     */
    private function isRetryable(int $status): bool
    {
        return $status === 429 || $status >= 500;
    }

    private function getRetryDelay(?ResponseInterface $response, int $attempt): int
    {
        $retryAfter = $response?->getHeaderLine('Retry-After') ?? '';

        if (ctype_digit($retryAfter)) {
            return min((int) $retryAfter, self::MAX_RETRY_DELAY);
        }

        return 2 ** $attempt;
    }

    /**
     * @throws ExtrarealityException
     */
    private function parseResponse(Request $request, ?ResponseInterface $response, bool $acceptBusinessErrors): array
    {
        if ($response === null) {
            throw new ExtrarealityException('No response received from ' . $request->getUri()->getPath());
        }

        $status = $response->getStatusCode();
        $data = json_decode($response->getBody(), true);

        if ($status >= 200 && $status < 300) {
            if (!is_array($data)) {
                throw new ExtrarealityException('Response is not valid JSON');
            }

            return $data;
        }

        // Partners may answer 403 or 422 with the same {"success": false, "message": ...} body they
        // used to return with a 200, and for some callers that body is the answer itself
        if ($acceptBusinessErrors && is_array($data) && array_key_exists('success', $data)) {
            return $data;
        }

        throw new ExtrarealityException(sprintf(
            'Request to %s failed with HTTP %d%s',
            // The path only: the query string carries a signature we would rather keep out of logs
            $request->getUri()->getPath(),
            $status,
            is_array($data) && isset($data['message']) ? ': ' . $data['message'] : ''
        ));
    }

    /**

     * HMAC-SHA256 over the canonical request string, as described in docs/RequestVerification.md
     */
    private function getSignature(string $timestamp, HttpMethod $method, string $url, ?string $body): string
    {
        $canonical = implode("\n", [
            $this->source,
            $timestamp,
            $method->value,
            $this->getRequestTarget($url),
            hash('sha256', $body ?? ''),
        ]);

        return hash_hmac('sha256', $canonical, $this->secret);
    }

    /**
     * Path and query string, the way the receiving side sees them in $_SERVER['REQUEST_URI']
     */
    private function getRequestTarget(string $url): string
    {
        $parts = parse_url($url);
        $target = $parts['path'] ?? '/';

        if (isset($parts['query'])) {
            $target .= '?' . $parts['query'];
        }

        return $target;
    }

    private function prepareRequest(HttpMethod $method,
                                    string $url,
                                    array $data = [],
                                    EndpointFormat $format = EndpointFormat::FORM
    ): Request {
        $timestamp = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        if ($method === HttpMethod::GET) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($data);
            $body = null;
        } else {
            $body = match ($format) {
                EndpointFormat::JSON => json_encode($data),
                default => http_build_query($data),
            };
        }

        // Signed last: it covers the body and the final URL, both of which are only known by now
        $isoTimestamp = $timestamp->format('c');

        $headers = [
            'X-Source' => $this->source,
            'X-Timestamp' => $isoTimestamp,
            'X-Signature-256' => $this->getSignature($isoTimestamp, $method, $url, $body),
        ];

        return new Request($method->value, $url, $headers, $body);
    }
}