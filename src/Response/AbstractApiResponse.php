<?php

namespace Extrareality\Response;

/**
 * Class AbstractApiResponse
 * @package Extrareality\Response
 */
abstract class AbstractApiResponse
{
    const TYPE_JSON = 'application/json';

    protected $contentType = 'text/html';
    protected $code;
    protected $message;
    protected $bookings = [];

    public function getCode()
    {
        return (int) $this->code;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function prepare()
    {
        $this->code = 200;
        $this->message = 'OK';
    }
}
