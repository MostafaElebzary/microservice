<?php

namespace App\Http\Controllers;

use App\Domains\Address\DTOs\AddressData;
use App\Domains\Address\Services\AddressService;
use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Http\Requests\AddressIdRequest;
use App\Http\Resources\AddressResource;
use App\Services\AuthServiceClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
class AddressController extends Controller
{
    public function __construct(
        private AddressService $addressService,
        private AuthServiceClient $authClient
    ) {
        $this->middleware('auth.rabbitmq');
        $this->middleware('throttle:60,1')->except(['index', 'show']);
    }

    // app/Http/Controllers/AddressController.php
    public function index(Request $request): JsonResponse
    {

        $user = $request->user;

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1'
        ]);

        $perPage = $validated['per_page'] ?? 10;

        $addresses = $this->addressService->paginateAddresses(
            $user['id'],
            $perPage
        );

        return response()->json(AddressResource::collection($addresses)->response()->getData(true), 200);
    }

    public function store(StoreAddressRequest $request): JsonResponse
    {
        $addressData = new AddressData(
            userId: $request->user()->id,
            street: $request->street,
            city: $request->city,
            state: $request->state,
            postalCode: $request->postal_code,
            country: $request->country,
            isPrimary: $request->boolean('is_primary', false)
        );

        $address = $this->addressService->createAddress($addressData);

        Log::info("Address created", ['address_id' => $address->id]);

        return response()->json(new AddressResource($address), 201);
    }

    public function show(AddressIdRequest $request, int $id): JsonResponse
    {
        $cacheKey = "user_{$request->user()->id}_address_{$id}";

        $address = Cache::remember($cacheKey, now()->addHour(), function() use ($request, $id) {
            return $this->addressService->getAddress($request->user()->id, $id);
        });

        return response()->json(new AddressResource($address));
    }

    public function update(UpdateAddressRequest $request, int $id): JsonResponse
    {
        $addressData = new AddressData(
            userId: $request->user()->id,
            street: $request->input('street'),
            city: $request->input('city'),
            state: $request->input('state'),
            postalCode: $request->input('postal_code'),
            country: $request->input('country'),
            isPrimary: $request->boolean('is_primary', false)
        );

        $address = $this->addressService->updateAddress(
            $request->user()->id,
            $id,
            $addressData
        );

        Cache::forget("user_{$request->user()->id}_address_{$id}");
        Cache::tags(["user_{$request->user()->id}_addresses"])->flush();

        return response()->json(new AddressResource($address));
    }

    public function destroy(AddressIdRequest $request, int $id): JsonResponse
    {
        Log::info("Deleting address", [
            'address_id' => $id,
            'user_id' => $request->user()->id
        ]);

        $this->addressService->deleteAddress($request->user()->id, $id);

        Cache::forget("user_{$request->user()->id}_address_{$id}");

        return response()->json(null, 204);
    }

    public function setPrimary(AddressIdRequest $request, int $id): JsonResponse
    {
        $address = $this->addressService->setPrimaryAddress(
            $request->user()->id,
            $id
        );

        // Clear all address caches for this user
        Cache::tags(["user_{$request->user()->id}_addresses"])->flush();

        return response()->json(new AddressResource($address));
    }
}
