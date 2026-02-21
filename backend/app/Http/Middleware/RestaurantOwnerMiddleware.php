<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestaurantOwnerMiddleware
{
    /**
     * Handle an incoming request.
     * Ensures the user is a restaurant owner or staff with access to a restaurant.
     * Injects the user's restaurant into the request for easy access in controllers.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user) {
            abort(403, 'Unauthorized.');
        }

        // Check if user has restaurant owner or staff permissions
        if (! $user->hasAnyPermission(['restaurant.manage', 'restaurant.owner'])) {
            abort(403, 'You do not have permission to manage a restaurant.');
        }

        // Get the user's restaurant (either owned or assigned)
        $restaurant = $this->getUserRestaurant($user);

        if (! $restaurant) {
            abort(403, 'No restaurant assigned to your account.');
        }

        // Verify restaurant is active and approved
        if (! $restaurant->is_active || $restaurant->status !== 'approved') {
            abort(403, 'Your restaurant is not active or approved.');
        }

        // Inject restaurant into request for easy access in controllers
        $request->merge(['_restaurant' => $restaurant]);
        $request->attributes->set('restaurant', $restaurant);

        return $next($request);
    }

    /**
     * Get the restaurant associated with the user.
     * Priority: owned restaurant > staff assigned restaurant
     */
    private function getUserRestaurant($user): ?\App\Models\Restaurant
    {
        // First check if user owns a restaurant
        $ownedRestaurant = $user->ownedRestaurants()->first();
        if ($ownedRestaurant) {
            return $ownedRestaurant;
        }

        // TODO: Check for staff assignment to a restaurant
        // This can be extended when staff-restaurant relationship is implemented

        return null;
    }
}
