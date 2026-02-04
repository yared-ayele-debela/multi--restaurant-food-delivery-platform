<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'user' => UserResource::make($request->user()->fresh())->resolve(),
        ]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->fill($request->validated());
        $user->save();

        return response()->json([
            'user' => UserResource::make($user->fresh())->resolve(),
        ]);
    }
}
