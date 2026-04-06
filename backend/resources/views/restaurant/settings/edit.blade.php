@extends('restaurant.layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Restaurant Profile Settings</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('restaurant.settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <h5 class="mb-3">Main Restaurant Information</h5>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Restaurant Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $restaurant->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Primary Phone</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $restaurant->phone) }}">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $restaurant->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="address_line" class="form-label">Address <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('address_line') is-invalid @enderror" id="address_line" name="address_line" value="{{ old('address_line', $restaurant->address_line) }}" required>
                        @error('address_line')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city', $restaurant->city) }}" required>
                                @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control @error('postal_code') is-invalid @enderror" id="postal_code" name="postal_code" value="{{ old('postal_code', $restaurant->postal_code) }}">
                                @error('postal_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control @error('country') is-invalid @enderror" id="country" name="country" value="{{ old('country', $restaurant->country) }}">
                                @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="latitude" class="form-label">Latitude</label>
                                <input type="number" step="any" class="form-control @error('latitude') is-invalid @enderror" id="latitude" name="latitude" value="{{ old('latitude', $restaurant->latitude) }}">
                                @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="longitude" class="form-label">Longitude</label>
                                <input type="number" step="any" class="form-control @error('longitude') is-invalid @enderror" id="longitude" name="longitude" value="{{ old('longitude', $restaurant->longitude) }}">
                                @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="main-location-search" class="form-label">Search Main Restaurant Location</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="main-location-search" placeholder="Search by address, area, or landmark">
                            <button type="button" class="btn btn-outline-primary" id="main-location-search-btn">Search</button>
                        </div>
                        <small class="text-muted">You can also click on the map to select your main restaurant location.</small>
                    </div>

                    <div class="mb-4">
                        <div id="main-location-map" style="height: 340px; border-radius: 8px;"></div>
                    </div>

                    <h5 class="mb-3">Order Settings</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="delivery_fee" class="form-label">Delivery Fee <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" class="form-control @error('delivery_fee') is-invalid @enderror" id="delivery_fee" name="delivery_fee" value="{{ old('delivery_fee', $restaurant->delivery_fee) }}" required>
                                @error('delivery_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="minimum_order_amount" class="form-label">Minimum Order Amount <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" class="form-control @error('minimum_order_amount') is-invalid @enderror" id="minimum_order_amount" name="minimum_order_amount" value="{{ old('minimum_order_amount', $restaurant->minimum_order_amount) }}" required>
                                @error('minimum_order_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h5 class="mb-3">Restaurant Images (Max 5)</h5>
                    <p class="text-muted mb-2">
                        Current images: <strong id="current-image-count">{{ $restaurant->images->count() }}</strong>/5
                        <span id="pending-image-count-wrap" class="ms-2 d-none">| Selected new: <strong id="pending-image-count">0</strong></span>
                    </p>
                    @error('images')
                        <div class="alert alert-danger py-2">{{ $message }}</div>
                    @enderror

                    @if($restaurant->images->isNotEmpty())
                        <div class="row g-3 mb-3">
                            @foreach($restaurant->images as $image)
                                <div class="col-md-4">
                                    <div class="border rounded p-2 h-100">
                                        <img src="{{ asset('storage/'.$image->image_path) }}" alt="{{ $image->alt_text ?: $restaurant->name }}" class="img-fluid rounded mb-2" style="width: 100%; height: 150px; object-fit: cover;">
                                        <div class="form-check mb-1">
                                            <input class="form-check-input" type="radio" name="primary_image_id" id="primary_image_{{ $image->id }}" value="{{ $image->id }}" {{ old('primary_image_id', $restaurant->images->firstWhere('is_primary', true)?->id) == $image->id ? 'checked' : '' }}>
                                            <label class="form-check-label" for="primary_image_{{ $image->id }}">Primary image</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="delete_image_ids[]" id="delete_image_{{ $image->id }}" value="{{ $image->id }}" {{ collect(old('delete_image_ids', []))->contains($image->id) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="delete_image_{{ $image->id }}">Delete image</label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="images" class="form-label">Upload New Images</label>
                        <input type="file" class="form-control @error('images') is-invalid @enderror @error('images.*') is-invalid @enderror" id="images" name="images[]" multiple accept=".jpg,.jpeg,.png,.webp,image/*">
                        <small class="text-muted">You can upload one or multiple files. Total images after save cannot exceed 5.</small>
                        @error('images.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3 d-none" id="upload-preview-wrapper">
                        <label class="form-label">Selected Image Preview (Before Upload)</label>
                        <div class="row g-3" id="upload-preview-grid"></div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i data-feather="save" class="me-1" style="width: 16px; height: 16px;"></i>
                            Save Settings
                        </button>
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
            const addressInput = document.getElementById('address_line');
            const cityInput = document.getElementById('city');
            const postalInput = document.getElementById('postal_code');
            const countryInput = document.getElementById('country');
            const searchInput = document.getElementById('main-location-search');
            const searchButton = document.getElementById('main-location-search-btn');
            const imagesInput = document.getElementById('images');
            const currentImageCountNode = document.getElementById('current-image-count');
            const pendingImageCountWrap = document.getElementById('pending-image-count-wrap');
            const pendingImageCountNode = document.getElementById('pending-image-count');
            const previewWrapper = document.getElementById('upload-preview-wrapper');
            const previewGrid = document.getElementById('upload-preview-grid');
            const maxImages = 5;
            const fallbackLat = 9.03;
            const fallbackLng = 38.74;
            const initialLat = parseFloat(latInput.value || fallbackLat);
            const initialLng = parseFloat(lngInput.value || fallbackLng);

            const map = L.map('main-location-map').setView([initialLat, initialLng], 13);
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
                if (countryInput) {
                    countryInput.value = properties.country || countryInput.value || '';
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
                if (countryInput) {
                    countryInput.value = properties.country || countryInput.value || '';
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

            function renderUploadPreview(files) {
                previewGrid.innerHTML = '';

                if (!files.length) {
                    previewWrapper.classList.add('d-none');
                    pendingImageCountWrap.classList.add('d-none');
                    pendingImageCountNode.textContent = '0';
                    return;
                }

                pendingImageCountNode.textContent = String(files.length);
                pendingImageCountWrap.classList.remove('d-none');
                previewWrapper.classList.remove('d-none');

                files.forEach(function (file) {
                    const col = document.createElement('div');
                    col.className = 'col-md-4';

                    const card = document.createElement('div');
                    card.className = 'border rounded p-2';

                    const image = document.createElement('img');
                    image.className = 'img-fluid rounded mb-2';
                    image.style.width = '100%';
                    image.style.height = '150px';
                    image.style.objectFit = 'cover';
                    image.alt = file.name;

                    const meta = document.createElement('small');
                    meta.className = 'text-muted d-block text-truncate';
                    meta.textContent = file.name;

                    const reader = new FileReader();
                    reader.onload = function (event) {
                        image.src = event.target?.result || '';
                    };
                    reader.readAsDataURL(file);

                    card.appendChild(image);
                    card.appendChild(meta);
                    col.appendChild(card);
                    previewGrid.appendChild(col);
                });
            }

            if (imagesInput) {
                imagesInput.addEventListener('change', function () {
                    const selectedFiles = Array.from(imagesInput.files || []);
                    const currentCount = parseInt(currentImageCountNode.textContent || '0', 10);
                    if ((currentCount + selectedFiles.length) > maxImages) {
                        alert(`You can select at most ${Math.max(maxImages - currentCount, 0)} more image(s).`);
                        imagesInput.value = '';
                        renderUploadPreview([]);
                        return;
                    }

                    renderUploadPreview(selectedFiles);
                });
            }
        })();
    </script>
@endpush
