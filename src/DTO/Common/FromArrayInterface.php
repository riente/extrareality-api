<?php

namespace Extrareality\DTO\Common;

interface FromArrayInterface
{
    public static function fromArray(array $data = []): AbstractApiDTO;
}