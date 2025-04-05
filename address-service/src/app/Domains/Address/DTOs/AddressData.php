<?php

namespace App\Domains\Address\DTOs;

class AddressData
{
    public function __construct(
        public readonly int $userId,
        public readonly string $street,
        public readonly string $city,
        public readonly string $state,
        public readonly string $postalCode,
        public readonly string $country,
        public readonly bool $isPrimary
    ) {}
}
