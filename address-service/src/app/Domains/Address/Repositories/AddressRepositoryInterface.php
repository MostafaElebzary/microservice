<?php

namespace App\Domains\Address\Repositories;

use App\Domains\Address\Models\Address;
use App\Domains\Address\DTOs\AddressData;
use Illuminate\Pagination\LengthAwarePaginator;

interface AddressRepositoryInterface
{
    public function create(AddressData $addressData): Address;
    public function update(Address $address, AddressData $addressData): Address;
    public function delete(Address $address): bool;
    public function findForUser(int $userId, int $addressId): ?Address;
    public function paginateForUser(int $userId, int $perPage = 10): LengthAwarePaginator;
    public function setPrimary(Address $address): void;
}
