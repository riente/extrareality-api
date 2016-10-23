<?php

namespace Extrareality\Response;

/**
 * Class PayResponse
 * @package Extrareality\Response
 */
class PayResponse extends AbstractApiResponse
{
    public function prepare()
    {
        $this->code = 200;
        $this->message = 'OK';
    }
}
