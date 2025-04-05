<?php

namespace App\Domains\Address\Services;

use App\Domains\Address\Repositories\AddressRepositoryInterface;
use App\Domains\Address\DTOs\AddressData;
use App\Domains\Address\Models\Address;
use App\Domains\Address\Exceptions\AddressNotFoundException;
use App\Domains\Address\Exceptions\UnauthorizedAddressAccessException;
use Illuminate\Pagination\LengthAwarePaginator;

class AddressService
{
    public function __construct(
        private AddressRepositoryInterface $repository
    ) {}

    public function createAddress(AddressData $addressData): Address
    {
        return $this->repository->create($addressData);
    }

    public function updateAddress(int $userId, int $addressId, AddressData $addressData): Address
    {
        $address = $this->getAddressForUser($userId, $addressId);
        return $this->repository->update($address, $addressData);
    }

    public function deleteAddress(int $userId, int $addressId): bool
    {
        $address = $this->getAddressForUser($userId, $addressId);
        return $this->repository->delete($address);
    }

    public function getAddress(int $userId, int $addressId): Address
    {
        return $this->getAddressForUser($userId, $addressId);
    }

    public function paginateAddresses(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->paginateForUser($userId, $perPage);
    }

    public function setPrimaryAddress(int $userId, int $addressId): Address
    {
        $address = $this->getAddressForUser($userId, $addressId);
        $this->repository->setPrimary($address);
        return $address->fresh();
    }

    private function getAddressForUser(int $userId, int $addressId): Address
    {
        $address = $this->repository->findForUser($userId, $addressId);

        if (!$address) {
            throw new AddressNotFoundException();
        }

        return $address;
    }
}
