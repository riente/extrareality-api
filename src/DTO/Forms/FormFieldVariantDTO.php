<?php

namespace Extrareality\DTO\Forms;

class FormFieldVariantDTO
{
    public function __construct(public int|string $value, public string $title)
    {
    }
}
