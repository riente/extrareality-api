<?php

namespace Extrareality\Response;

/**
 * Class CancelResponse
 * @package Extrareality\Response
 */
class CancelResponse extends AbstractApiResponse
{
    /**
     * @param bool $bookingFoundAndCancelled Whether a booking record was found in the DB and successfully cancelled
     */
    public function prepare($bookingFoundAndCancelled = true)
    {
        if ($bookingFoundAndCancelled) {
            $this->code = 200;
            $this->message = 'OK';
        } else {
            $this->code = 400;
            $this->message = 'Бронь не найдена.';
        }
    }
}
