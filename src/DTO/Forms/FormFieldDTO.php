<?php

namespace Extrareality\DTO\Forms;

use Extrareality\Enums\FormFieldType;

class FormFieldDTO
{
    /** @var array<FormFieldVariantDTO>|null  */
    public ?array $variants = null;

    public function __construct(
        public FormFieldType $type,
        public string $name,
        public string $title,
        public bool $required = false,
        public ?string $description = null,
        ?array $variants = null,
        /**
         * Predefined value of a "checkbox" or a "hidden" field. It is what we send back under
         * $name when the form is submitted, so a hidden field without it has nothing to carry
         */
        public int|float|string|null $value = null,
        /** Longest accepted text for "text", largest accepted number for "number" */
        public int|float|null $max = null,
    ) {
        // If there are variants, check the validity of the structure
        if (!empty($variants)) {
            $variants = array_filter(
                $variants,
                function ($variant) {
                    return $variant instanceof FormFieldVariantDTO
                        || (is_array($variant) && isset($variant['value']) && !empty($variant['title']));
                },
            );

            // Make the array contain only DTOs
            $this->variants = array_map(
                fn($variant) => is_array($variant) ? new FormFieldVariantDTO(...$variant) : $variant,
                $variants
            );
        }
    }
}
