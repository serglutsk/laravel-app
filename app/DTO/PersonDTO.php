<?php

namespace App\DTO;

use JsonSerializable;

/**
 * Data Transfer Object for representing a person.
 */
readonly class PersonDTO implements JsonSerializable
{
    public function __construct(
        public ?string $title,
        public ?string $first_name,
        public ?string $initial,
        public ?string $last_name
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'title' => $this->title,
            'first_name' => $this->first_name,
            'initial' => $this->initial,
            'last_name' => $this->last_name,
        ];
    }
}
