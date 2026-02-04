<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreUserAddressRequest;
use App\Http\Requests\Api\V1\UpdateUserAddressRequest;
use App\Models\UserAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserAddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', UserAddress::class);

        $addresses = $request->user()
            ->addresses()
            ->orderByDesc('is_default')
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'data' => $addresses->map(fn (UserAddress $a) => $this->addressPayload($a)),
        ]);
    }

    public function store(StoreUserAddressRequest $request): JsonResponse
    {
        $this->authorize('create', UserAddress::class);

        $user = $request->user();
        $data = $request->validated();
        $data['country'] = $data['country'] ?? 'US';

        if (! isset($data['is_default'])) {
            $data['is_default'] = ! $user->addresses()->exists();
        }

        $address = DB::transaction(function () use ($user, $data) {
            $address = $user->addresses()->create($data);

            if ($address->is_default) {
                $this->clearOtherDefaults($user, $address);
            }

            return $address;
        });

        return response()->json([
            'data' => $this->addressPayload($address->fresh()),
        ], 201);
    }

    public function update(UpdateUserAddressRequest $request, UserAddress $address): JsonResponse
    {
        $this->authorize('update', $address);

        $data = $request->validated();

        $user = $request->user();

        DB::transaction(function () use ($user, $address, $data) {
            $address->fill($data);
            $address->save();

            if ($address->is_default) {
                $this->clearOtherDefaults($user, $address);
            }
        });

        return response()->json([
            'data' => $this->addressPayload($address->fresh()),
        ]);
    }

    public function destroy(Request $request, UserAddress $address): JsonResponse
    {
        $this->authorize('delete', $address);

        $address->delete();

        return response()->json(null, 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function addressPayload(UserAddress $address): array
    {
        return [
            'id' => $address->id,
            'label' => $address->label,
            'address_line_1' => $address->address_line_1,
            'address_line_2' => $address->address_line_2,
            'city' => $address->city,
            'state' => $address->state,
            'postal_code' => $address->postal_code,
            'country' => $address->country,
            'latitude' => (string) $address->latitude,
            'longitude' => (string) $address->longitude,
            'is_default' => $address->is_default,
            'instructions' => $address->instructions,
        ];
    }

    private function clearOtherDefaults(\App\Models\User $user, UserAddress $keep): void
    {
        $user->addresses()
            ->where('id', '!=', $keep->id)
            ->update(['is_default' => false]);
    }
}
