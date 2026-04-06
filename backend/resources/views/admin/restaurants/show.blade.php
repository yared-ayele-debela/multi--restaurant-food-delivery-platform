@extends('admin.layouts.app')

@section('title')
    {{ $restaurant->name }} - Restaurant Details
@endsection

@section('content')
<div class="container-fluid">
    <x-page-title
        title="Restaurant Details"
        :breadcrumbs="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Restaurants', 'url' => route('admin.restaurants.index')],
            ['label' => $restaurant->name],
        ]"
    />

    <x-alert />

    <!-- Restaurant Header Card -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-1">{{ $restaurant->name }}</h2>
                            <p class="text-muted mb-2">{{ $restaurant->slug }}</p>
                            <div class="d-flex gap-2 mb-2">
                                <span class="badge bg-{{ $restaurant->status === 'approved' ? 'success' : ($restaurant->status === 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($restaurant->status) }}
                                </span>
                                @if($restaurant->is_active)
                                    <span class="badge bg-info">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                                @if($restaurant->is_featured)
                                    <span class="badge bg-primary">Featured</span>
                                @endif
                            </div>
                            <p class="mb-0"><i class="mdi mdi-map-marker me-1"></i> {{ $restaurant->address_line }}, {{ $restaurant->city }}, {{ $restaurant->postal_code }}</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <p class="mb-1"><strong>Owner:</strong> {{ $restaurant->owner?->name ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Email:</strong> {{ $restaurant->owner?->email ?? 'N/A' }}</p>
                            <p class="mb-0"><strong>Phone:</strong> {{ $restaurant->phone }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <span class="text-muted mb-3 lh-1 d-block">Total Orders</span>
                            <h4 class="mb-3">{{ number_format($stats['total_orders']) }}</h4>
                        </div>
                        <div class="col-4 text-end">
                            <div class="avatar-md">
                                <span class="avatar-title rounded-circle bg-primary-subtle text-primary font-size-24">
                                    <i class="mdi mdi-cart"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <span class="text-muted mb-3 lh-1 d-block">Total Revenue</span>
                            <h4 class="mb-3">${{ number_format($stats['total_revenue'], 2) }}</h4>
                        </div>
                        <div class="col-4 text-end">
                            <div class="avatar-md">
                                <span class="avatar-title rounded-circle bg-success-subtle text-success font-size-24">
                                    <i class="mdi mdi-currency-usd"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <span class="text-muted mb-3 lh-1 d-block">Products</span>
                            <h4 class="mb-3">{{ number_format($stats['total_products']) }}</h4>
                        </div>
                        <div class="col-4 text-end">
                            <div class="avatar-md">
                                <span class="avatar-title rounded-circle bg-info-subtle text-info font-size-24">
                                    <i class="mdi mdi-food"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <span class="text-muted mb-3 lh-1 d-block">Branches</span>
                            <h4 class="mb-3">{{ $restaurant->branches->count() }}</h4>
                        </div>
                        <div class="col-4 text-end">
                            <div class="avatar-md">
                                <span class="avatar-title rounded-circle bg-warning-subtle text-warning font-size-24">
                                    <i class="mdi mdi-store-marker"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Row -->
    <div class="row">
        <!-- Restaurant Info -->
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Restaurant Images</h4>
                    <span class="badge bg-primary">{{ $restaurant->images->count() }}/5</span>
                </div>
                <div class="card-body">
                    @if($restaurant->images->count() > 0)
                        <div class="row g-2">
                            @foreach($restaurant->images as $image)
                                <div class="col-6">
                                    <div class="position-relative">
                                        <img src="{{ asset('storage/'.$image->image_path) }}" alt="{{ $image->alt_text ?: $restaurant->name }}" class="img-fluid rounded border" style="width: 100%; height: 120px; object-fit: cover;">
                                        @if($image->is_primary)
                                            <span class="badge bg-success position-absolute" style="top: 8px; left: 8px;">Primary</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">No restaurant images uploaded yet.</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Restaurant Information</h4>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted">Description</td>
                            <td>{{ $restaurant->description ?? 'No description' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Phone</td>
                            <td>{{ $restaurant->phone }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Address</td>
                            <td>{{ $restaurant->address_line }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">City</td>
                            <td>{{ $restaurant->city }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Postal Code</td>
                            <td>{{ $restaurant->postal_code }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Coordinates</td>
                            <td>{{ $restaurant->latitude }}, {{ $restaurant->longitude }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Delivery Fee</td>
                            <td>${{ number_format($restaurant->delivery_fee, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Min Order</td>
                            <td>${{ number_format($restaurant->minimum_order_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Commission</td>
                            <td>{{ $restaurant->commission_rate }}%</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Categories -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Categories ({{ $stats['total_categories'] }})</h4>
                </div>
                <div class="card-body">
                    @if($restaurant->categories->count() > 0)
                        <div class="list-group">
                            @foreach($restaurant->categories as $category)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $category->name }}
                                    <span class="badge bg-primary rounded-pill">{{ $category->products_count }} products</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">No categories found.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Branches -->
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Restaurant & Branch Locations</h4>
                </div>
                <div class="card-body">
                    <div id="admin-restaurant-location-map" style="height: 360px; border-radius: 8px;"></div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Branches</h4>
                    <span class="badge bg-primary">{{ $restaurant->branches->count() }} total</span>
                </div>
                <div class="card-body">
                    @if($restaurant->branches->count() > 0)
                        <div class="row">
                            @foreach($restaurant->branches as $branch)
                                <div class="col-md-6 mb-3">
                                    <div class="card border h-100">
                                        <div class="card-body">
                                            @if($branch->image_path)
                                                <img src="{{ asset('storage/'.$branch->image_path) }}" alt="{{ $branch->name }}" class="img-fluid rounded mb-3 border" style="width: 100%; height: 140px; object-fit: cover;">
                                            @endif
                                            <h5 class="card-title">{{ $branch->name }}</h5>
                                            <p class="card-text">
                                                <i class="mdi mdi-map-marker me-1 text-primary"></i>
                                                {{ $branch->address }}, {{ $branch->city }}
                                            </p>
                                            <p class="card-text mb-1">
                                                <i class="mdi mdi-phone me-1 text-success"></i>
                                                {{ $branch->phone ?? 'N/A' }}
                                            </p>
                                            <p class="card-text mb-0">
                                                <i class="mdi mdi-crosshairs-gps me-1 text-warning"></i>
                                                {{ $branch->latitude }}, {{ $branch->longitude }}
                                            </p>
                                            <div class="mt-2">
                                                @if($branch->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                                @if($branch->is_main_branch)
                                                    <span class="badge bg-primary">Main Branch</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-store-marker-off font-size-48 text-muted"></i>
                            <p class="text-muted mb-0 mt-2">No branches found for this restaurant.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Operating Hours -->
            @if($restaurant->hours->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Operating Hours</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Day</th>
                                        <th>Open</th>
                                        <th>Close</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($restaurant->hours as $hour)
                                        <tr>
                                            <td>{{ ucfirst($hour->day_of_week) }}</td>
                                            <td>{{ $hour->open_time ?? '-' }}</td>
                                            <td>{{ $hour->close_time ?? '-' }}</td>
                                            <td>
                                                @if($hour->is_closed)
                                                    <span class="badge bg-secondary">Closed</span>
                                                @else
                                                    <span class="badge bg-success">Open</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.restaurants.index') }}" class="btn btn-secondary">
                            <i class="mdi mdi-arrow-left me-1"></i> Back to List
                        </a>
                        @if($restaurant->status !== \App\Models\Restaurant::STATUS_APPROVED)
                            <form action="{{ route('admin.restaurants.approve', $restaurant) }}" method="post" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="mdi mdi-check me-1"></i> Approve
                                </button>
                            </form>
                        @endif
                        @if($restaurant->status !== \App\Models\Restaurant::STATUS_REJECTED)
                            <form action="{{ route('admin.restaurants.reject', $restaurant) }}" method="post" class="d-inline" onsubmit="return confirm('Reject this restaurant?');">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="mdi mdi-close me-1"></i> Reject
                                </button>
                            </form>
                        @endif
                        @if($restaurant->status !== \App\Models\Restaurant::STATUS_SUSPENDED)
                            <form action="{{ route('admin.restaurants.suspend', $restaurant) }}" method="post" class="d-inline" onsubmit="return confirm('Suspend this restaurant?');">
                                @csrf
                                <button type="submit" class="btn btn-warning">
                                    <i class="mdi mdi-pause me-1"></i> Suspend
                                </button>
                            </form>
                        @endif
                        <form action="{{ route('admin.restaurants.toggle-featured', $restaurant) }}" method="post" class="d-inline ms-auto">
                            @csrf
                            <button type="submit" class="btn btn-soft-primary">
                                {{ $restaurant->is_featured ? 'Unfeature' : 'Feature' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@push('scripts')
    @php
        $branchesForMap = $restaurant->branches->map(function ($branch) {
            return [
                'name' => $branch->name,
                'address' => $branch->address,
                'city' => $branch->city,
                'lat' => $branch->latitude,
                'lng' => $branch->longitude,
            ];
        })->values();
    @endphp
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        (function () {
            const mainLat = Number(@json($restaurant->latitude));
            const mainLng = Number(@json($restaurant->longitude));
            const hasMainCoords = Number.isFinite(mainLat) && Number.isFinite(mainLng);
            const fallbackCenter = [9.03, 38.74];

            const map = L.map('admin-restaurant-location-map').setView(
                hasMainCoords ? [mainLat, mainLng] : fallbackCenter,
                hasMainCoords ? 12 : 6
            );

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            const points = [];
            const restaurantIcon = L.divIcon({
                className: 'restaurant-main-marker',
                html: '<div style="width:34px;height:34px;border-radius:50%;background:#556ee6;color:#fff;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,.25);"><i class="mdi mdi-storefront" style="font-size:18px;line-height:1;"></i></div>',
                iconSize: [34, 34],
                iconAnchor: [17, 17],
                popupAnchor: [0, -14],
            });
            const branchIcon = L.divIcon({
                className: 'restaurant-branch-marker',
                html: '<div style="width:30px;height:30px;border-radius:50%;background:#f1b44c;color:#fff;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,.22);"><i class="mdi mdi-map-marker-radius" style="font-size:16px;line-height:1;"></i></div>',
                iconSize: [30, 30],
                iconAnchor: [15, 15],
                popupAnchor: [0, -12],
            });

            if (hasMainCoords) {
                const mainMarker = L.marker([mainLat, mainLng], { icon: restaurantIcon }).addTo(map);
                mainMarker.bindPopup(`<strong>Main Restaurant</strong><br>{{ e($restaurant->name) }}<br>{{ e($restaurant->address_line) }}, {{ e($restaurant->city) }}`);
                points.push([mainLat, mainLng]);
            }

            const branches = @json($branchesForMap);

            branches.forEach(function (branch) {
                const lat = Number(branch.lat);
                const lng = Number(branch.lng);
                if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                    return;
                }
                const marker = L.marker([lat, lng], { icon: branchIcon }).addTo(map);
                marker.bindPopup(`<strong>Branch:</strong> ${branch.name}<br>${branch.address}, ${branch.city}`);
                points.push([lat, lng]);
            });

            if (points.length > 1) {
                map.fitBounds(points, { padding: [30, 30] });
            }
        })();
    </script>
@endpush
