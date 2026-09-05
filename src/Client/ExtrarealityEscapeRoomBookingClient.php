<?php

namespace Extrareality\Client;

use Extrareality\DTO\EscapeRoom\BookingCancelRequestDTO;
use Extrareality\DTO\EscapeRoom\BookingOperationResultDTO;
use Extrareality\DTO\EscapeRoom\BookingRequestDTO;
use Extrareality\DTO\EscapeRoom\BookingUpdateRequestDTO;
use Extrareality\DTO\EscapeRoom\QuestRatingDTO;
use Extrareality\DTO\EscapeRoom\ReviewDTO;
use Extrareality\DTO\EscapeRoom\ScheduleSlotDTO;
use Extrareality\Exceptions\ExtrarealityException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;

/**
 * Client for Escape Room Booking API v2.
 *
 * Outbound: fetch reviews and quest ratings from ExtraReality.
 * Inbound helpers: verify signatures and parse webhook payloads from ExtraReality.
 *
 * @see docs/APIv2.md
 */
class ExtrarealityEscapeRoomBookingClient
{
    private const DEFAULT_BASE_URL = 'https://extrareality.pl';
    private const REVIEWS_PATH = '/api2/reviews';
    private const RATING_PATH = '/api2/rating';
    private const SOURCE = 'extrareality';

    private Client $httpClient;

    /**
     * @param string $secret Shared secret for verifying incoming requests (md5 signature)
     * @param string $baseUrl ExtraReality site root, used for reviews and rating endpoints
     */
    public function __construct(
        private readonly string $secret,
        private readonly string $baseUrl = self::DEFAULT_BASE_URL,
        ?Client $httpClient = null,
    ) {
        $this->httpClient = $httpClient ?? new Client([
            'timeout' => 10.0,
            'http_errors' => false,
        ]);
    }

    /**
     * @return ReviewDTO[]
     * @throws ExtrarealityException
     * @throws GuzzleException|JsonException
     * @see docs/APIv2.md#fetch-review-list
     */
    public function getReviews(
        int $questId,
        ?int $newerThanId = null,
        ?int $quantity = null,
        int|float|null $ratingThreshold = null,
    ): array {
        $query = ['quest_id' => $questId];

        if ($newerThanId !== null) {
            $query['newer_than_id'] = $newerThanId;
        }

        if ($quantity !== null) {
            $query['quantity'] = $quantity;
        }

        if ($ratingThreshold !== null) {
            $query['rating_threshold'] = $ratingThreshold;
        }

        $data = $this->sendGet(self::REVIEWS_PATH, $query);

        if (!is_array($data)) {
            throw new ExtrarealityException('Reviews response is not a JSON array');
        }

        return array_map(static fn(array $item) => new ReviewDTO($item), $data);
    }

    /**
     * @throws ExtrarealityException
     * @throws GuzzleException|JsonException
     * @see docs/APIv2.md#fetch-quest-rating
     */
    public function getRating(int $questId): QuestRatingDTO
    {
        $data = $this->sendGet(self::RATING_PATH, [
            'quest_id' => $questId,
            'json' => 1,
        ]);

        if (!is_array($data)) {
            throw new ExtrarealityException('Rating response is not a JSON object');
        }

        return new QuestRatingDTO($data);
    }

    /**
     * md5($datetime . $secret), as sent in incoming webhook payloads.
     *
     * @see docs/APIv2.md
     */
    public function generateSignature(string $datetime): string
    {
        return md5($datetime . $this->secret);
    }

    public function verifySignature(string $datetime, string $signature): bool
    {
        if ($datetime === '' || $signature === '') {
            return false;
        }

        return hash_equals($this->generateSignature($datetime), $signature);
    }

    public function verifyIncomingPayload(array $payload): bool
    {
        return $this->verifySignature(
            (string) ($payload['datetime'] ?? ''),
            (string) ($payload['signature'] ?? ''),
        );
    }

    public function parseBookingRequest(array $payload): BookingRequestDTO
    {
        return new BookingRequestDTO($payload);
    }

    public function parseBookingUpdateRequest(array $payload): BookingUpdateRequestDTO
    {
        return new BookingUpdateRequestDTO($payload);
    }

    public function parseBookingCancelRequest(array $payload): BookingCancelRequestDTO
    {
        return new BookingCancelRequestDTO($payload);
    }

    /**
     * @param ScheduleSlotDTO[] $slots
     */
    public function serializeSchedule(array $slots): string
    {
        return json_encode(
            array_map(static fn(ScheduleSlotDTO $slot) => $slot->jsonSerialize(), $slots),
            JSON_THROW_ON_ERROR
        );
    }

    public function bookingSuccessResponse(): BookingOperationResultDTO
    {
        return BookingOperationResultDTO::success();
    }

    public function bookingFailureResponse(string $message): BookingOperationResultDTO
    {
        return BookingOperationResultDTO::failure($message);
    }

    /**
     * Whether the payload looks like it came from ExtraReality (source field check).
     */
    public function isFromExtrareality(array $payload): bool
    {
        return ($payload['source'] ?? null) === self::SOURCE;
    }

    /**
     * @throws ExtrarealityException
     * @throws GuzzleException|JsonException
     */
    private function sendGet(string $path, array $query): mixed
    {
        $url = rtrim($this->baseUrl, '/') . $path;
        $response = $this->httpClient->get($url, ['query' => $query]);
        $status = $response->getStatusCode();
        $body = (string) $response->getBody();

        if ($status < 200 || $status >= 300) {
            throw new ExtrarealityException(sprintf(
                'GET %s failed with HTTP %d',
                $path,
                $status
            ));
        }

        return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    }
}
