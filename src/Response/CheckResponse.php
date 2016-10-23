<?php

namespace Extrareality\Response;

/**
 * Class CheckResponse
 * @package Extrareality\Response
 */
class CheckResponse extends AbstractApiResponse
{
    /**
     * @param bool  $isBooked     If a booking record exists
     * @param mixed $bookingName  Person's name; considered only if $isBooked is true
     * @param mixed $bookingPhone Person's phone; considered only if $isBooked is true
     */
    public function prepare($isBooked = false, $bookingName = null, $bookingPhone = null)
    {
        $this->contentType = static::TYPE_JSON;
        $this->code = 200;

        if ($isBooked) {
            $response = [
                'booked' => 1,
                'name' => $bookingName,
                'phone' => $bookingPhone,
            ];
        } else {
            $response = [
                'booked' => 0,
            ];
        }

        $this->message = json_encode($response);
    }
}
