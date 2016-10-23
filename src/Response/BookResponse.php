<?php

namespace Extrareality\Response;

/**
 * Class BookResponse
 * @package Extrareality\Response
 */
class BookResponse extends AbstractApiResponse
{
    /**
     * @param mixed $errorMessage If booking is successful, then $errorMessage should be null
     * @param mixed $errorCode    Considered only if error message is not empty
     */
    public function prepare($errorMessage = null, $errorCode = null)
    {
        if ($errorMessage) {
            $this->code = (int) $errorCode;
            $this->message = (string) $errorMessage;
        } else {
            $this->code = 200;
            $this->message = 'OK';
        }
    }
}
