@extends('restaurant.layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Edit Branch</h4>
            <div class="page-title-right">
                <a href="{{ route('restaurant.branches.index') }}" class="btn btn-secondary">
                    <i data-feather="arrow-left" class="me-1" style="width: 16px; height: 16px;"></i>
                    Back to Branches
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('restaurant.branches.update', $branch) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Branch Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $branch->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $branch->phone) }}">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" value="{{ old('address', $branch->address) }}" required>
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city', $branch->city) }}" required>
                                @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control @error('state') is-invalid @enderror" id="state" name="state" value="{{ old('state', $branch->state) }}">
                                @error('state')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="postal_code" class="form-label">Postal Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('postal_code') is-invalid @enderror" id="postal_code" name="postal_code" value="{{ old('postal_code', $branch->postal_code) }}" required>
                                @error('postal_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="latitude" class="form-label">Latitude</label>
                                <input type="number" step="any" class="form-control @error('latitude') is-invalid @enderror" id="latitude" name="latitude" value="{{ old('latitude', $branch->latitude) }}">
                                @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="longitude" class="form-label">Longitude</label>
                                <input type="number" step="any" class="form-control @error('longitude') is-invalid @enderror" id="longitude" name="longitude" value="{{ old('longitude', $branch->longitude) }}">
                                @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Branch Image</label>
                        <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept=".jpg,.jpeg,.png,.webp,image/*">
                        @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3 d-none" id="branch-image-preview-wrap">
                        <label class="form-label">Selected Image Preview</label>
                        <div class="border rounded p-2">
                            <img id="branch-image-preview" src="" alt="Branch preview" class="img-fluid rounded" style="max-height: 160px;">
                            <small id="branch-image-preview-name" class="text-muted d-block mt-2"></small>
                        </div>
                    </div>

                    @if($branch->image_path)
                        <div class="mb-3">
                            <img src="{{ asset('storage/'.$branch->image_path) }}" alt="{{ $branch->name }}" class="img-fluid rounded border" style="max-height: 140px;">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image" value="1" {{ old('remove_image') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remove_image">Remove current image</label>
                            </div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="branch-location-search" class="form-label">Search Branch Location</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="branch-location-search" placeholder="Search by address, area, or landmark">
                            <button type="button" class="btn btn-outline-primary" id="branch-location-search-btn">Search</button>
                        </div>
                        <small class="text-muted">You can also click on the map to select branch coordinates.</small>
                    </div>

                    <div class="mb-3">
                        <div id="branch-location-map" style="height: 320px; border-radius: 8px;"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="delivery_radius" class="form-label">Delivery Radius (km)</label>
                                <input type="number" step="0.1" class="form-control @error('delivery_radius') is-invalid @enderror" id="delivery_radius" name="delivery_radius" value="{{ old('delivery_radius', $branch->delivery_radius) }}">
                                @error('delivery_radius')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="preparation_time" class="form-label">Prep Time (min)</label>
                                <input type="number" class="form-control @error('preparation_time') is-invalid @enderror" id="preparation_time" name="preparation_time" value="{{ old('preparation_time', $branch->preparation_time) }}">
                                @error('preparation_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label d-block">Status</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $branch->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i data-feather="save" class="me-1" style="width: 16px; height: 16px;"></i>
                            Update Branch
                        </button>
                        <a href="{{ route('restaurant.branches.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        (function () {
            const apiKey = @json($geoapifyKey ?? '');
            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');
            const addressInput = document.getElementById('address');
            const cityInput = document.getElementById('city');
            const postalInput = document.getElementById('postal_code');
            const stateInput = document.getElementById('state');
            const searchInput = document.getElementById('branch-location-search');
            const searchButton = document.getElementById('branch-location-search-btn');
            const imageInput = document.getElementById('image');
            const imagePreviewWrap = document.getElementById('branch-image-preview-wrap');
            const imagePreview = document.getElementById('branch-image-preview');
            const imagePreviewName = document.getElementById('branch-image-preview-name');
            const fallbackLat = 9.03;
            const fallbackLng = 38.74;
            const initialLat = parseFloat(latInput.value || fallbackLat);
            const initialLng = parseFloat(lngInput.value || fallbackLng);

            const map = L.map('branch-location-map').setView([initialLat, initialLng], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            let marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);

            function setCoords(lat, lng) {
                latInput.value = Number(lat).toFixed(8);
                lngInput.value = Number(lng).toFixed(8);
                marker.setLatLng([lat, lng]);
            }

            async function reverseGeocode(lat, lng) {
                if (!apiKey) {
                    return;
                }
                const url = `https://api.geoapify.com/v1/geocode/reverse?lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}&apiKey=${encodeURIComponent(apiKey)}`;
                const response = await fetch(url);
                if (!response.ok) {
                    return;
                }
                const data = await response.json();
                const feature = data?.features?.[0];
                if (!feature) {
                    return;
                }
                const properties = feature.properties || {};
                if (addressInput && !addressInput.value) {
                    addressInput.value = properties.formatted || '';
                }
                if (cityInput) {
                    cityInput.value = properties.city || properties.county || cityInput.value || '';
                }
                if (postalInput) {
                    postalInput.value = properties.postcode || postalInput.value || '';
                }
                if (stateInput) {
                    stateInput.value = properties.state || stateInput.value || '';
                }
            }

            async function searchLocation() {
                const query = (searchInput.value || '').trim();
                if (!query || !apiKey) {
                    return;
                }
                const url = `https://api.geoapify.com/v1/geocode/autocomplete?text=${encodeURIComponent(query)}&limit=1&apiKey=${encodeURIComponent(apiKey)}`;
                const response = await fetch(url);
                if (!response.ok) {
                    return;
                }
                const data = await response.json();
                const feature = data?.features?.[0];
                if (!feature) {
                    return;
                }
                const [lng, lat] = feature.geometry.coordinates;
                map.setView([lat, lng], 15);
                setCoords(lat, lng);
                const properties = feature.properties || {};
                if (addressInput) {
                    addressInput.value = properties.formatted || addressInput.value || '';
                }
                if (cityInput) {
                    cityInput.value = properties.city || properties.county || cityInput.value || '';
                }
                if (postalInput) {
                    postalInput.value = properties.postcode || postalInput.value || '';
                }
                if (stateInput) {
                    stateInput.value = properties.state || stateInput.value || '';
                }
            }

            map.on('click', async function (event) {
                const { lat, lng } = event.latlng;
                setCoords(lat, lng);
                await reverseGeocode(lat, lng);
            });

            marker.on('dragend', async function (event) {
                const point = event.target.getLatLng();
                setCoords(point.lat, point.lng);
                await reverseGeocode(point.lat, point.lng);
            });

            searchButton.addEventListener('click', searchLocation);
            searchInput.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    searchLocation();
                }
            });

            if (imageInput) {
                imageInput.addEventListener('change', function () {
                    const file = imageInput.files && imageInput.files[0] ? imageInput.files[0] : null;
                    if (!file) {
                        imagePreviewWrap.classList.add('d-none');
                        imagePreview.src = '';
                        imagePreviewName.textContent = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function (event) {
                        imagePreview.src = event.target?.result || '';
                        imagePreviewName.textContent = file.name;
                        imagePreviewWrap.classList.remove('d-none');
                    };
                    reader.readAsDataURL(file);
                });
            }
        })();
    </script>
@endpush
