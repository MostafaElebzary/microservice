<?php

namespace App\Domains\Address\Repositories;

use App\Domains\Address\Models\Address;
use App\Domains\Address\DTOs\AddressData;
use App\Domains\Address\Exceptions\AddressNotFoundException;

class EloquentAddressRepository implements AddressRepositoryInterface
{
    public function create(AddressData $addressData): Address
    {
        return Address::create([
            'user_id' => $addressData->userId,
            'street' => $addressData->street,
            'city' => $addressData->city,
            'state' => $addressData->state,
            'postal_code' => $addressData->postalCode,
            'country' => $addressData->country,
            'is_primary' => $addressData->isPrimary
        ]);
    }

    public function update(Address $address, AddressData $addressData): Address
    {
        $address->update([
            'street' => $addressData->street,
            'city' => $addressData->city,
            'state' => $addressData->state,
            'postal_code' => $addressData->postalCode,
            'country' => $addressData->country,
            'is_primary' => $addressData->isPrimary
        ]);

        return $address->fresh();
    }

    public function delete(Address $address): bool
    {
        return $address->delete();
    }

    public function findForUser(int $userId, int $addressId): ?Address
    {
        return Address::where('user_id', $userId)
            ->where('id', $addressId)
            ->first();
    }


    public function paginateForUser(int $userId, int $perPage = 10): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Address::where('user_id', $userId)
            ->orderBy('is_primary', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function setPrimary(Address $address): void
    {
        Address::where('user_id', $address->user_id)
            ->update(['is_primary' => false]);

        $address->update(['is_primary' => true]);
    }
}
