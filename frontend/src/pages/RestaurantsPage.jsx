import { useEffect, useMemo, useRef, useState } from "react";
import PageScaffold from "../components/PageScaffold";
import { Card, Drawer, Skeleton } from "../components/ui/primitives";
import { fetchRestaurants, toMediaUrl } from "../api/catalogApi";
import { useLocationContext } from "../context/LocationContext";
import { haversineDistanceKm, parseCoordinate } from "../utils/location";
import RestaurantCard from "../components/cards/RestaurantCard";
import L from "leaflet";
import "leaflet/dist/leaflet.css";

function toCurrency(value) {
  const amount = Number.parseFloat(value || "0");
  return `$${amount.toFixed(2)}`;
}

function toRestaurantDistance(restaurant, location) {
  if (!location) {
    return null;
  }
  return haversineDistanceKm(
    { lat: location.lat, lng: location.lng },
    { lat: parseCoordinate(restaurant.latitude), lng: parseCoordinate(restaurant.longitude) },
  );
}

export default function RestaurantsPage() {
  const [search, setSearch] = useState("");
  const [sortBy, setSortBy] = useState("name_asc");
  const [deliveryFeeRange, setDeliveryFeeRange] = useState("all");
  const [minOrderRange, setMinOrderRange] = useState("all");
  const [cityFilter, setCityFilter] = useState("all");
  const [statusFilter, setStatusFilter] = useState("all");
  const [favoritesOnly, setFavoritesOnly] = useState(false);
  const [freeDeliveryOnly, setFreeDeliveryOnly] = useState(false);
  const [favorites, setFavorites] = useState([]);
  const [showFilters, setShowFilters] = useState(false);
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [isError, setIsError] = useState(false);
  const [mapAreaEnabled, setMapAreaEnabled] = useState(false);
  const [radiusKm, setRadiusKm] = useState(5);
  const [selectedAreaCenter, setSelectedAreaCenter] = useState(null);
  const [page, setPage] = useState(1);
  const [meta, setMeta] = useState({ currentPage: 1, lastPage: 1 });
  const [restaurants, setRestaurants] = useState([]);
  const areaMapRef = useRef(null);
  const areaMarkerRef = useRef(null);
  const areaCircleRef = useRef(null);
  const { location, openPicker } = useLocationContext();
  const areaMarkerIcon = useMemo(
    () =>
      L.divIcon({
        className: "fd-area-marker",
        html: '<div style="width:28px;height:28px;border-radius:999px;background:#D96C4A;border:2px solid #FFF8F0;box-shadow:0 3px 10px rgba(0,0,0,0.2);"></div>',
        iconSize: [28, 28],
        iconAnchor: [14, 14],
      }),
    [],
  );

  useEffect(() => {
    let mounted = true;
    async function loadRestaurants() {
      try {
        setIsLoading(true);
        setIsError(false);
        const response = await fetchRestaurants({ page, perPage: 9 });
        if (!mounted) {
          return;
        }
        setRestaurants(response.items || []);
        setMeta(response.meta || { currentPage: 1, lastPage: 1 });
      } catch {
        if (mounted) {
          setIsError(true);
        }
      } finally {
        if (mounted) {
          setIsLoading(false);
        }
      }
    }
    loadRestaurants();
    return () => {
      mounted = false;
    };
  }, [page]);

  useEffect(() => {
    if (!selectedAreaCenter && location) {
      setSelectedAreaCenter({ lat: location.lat, lng: location.lng });
    }
  }, [location, selectedAreaCenter]);

  useEffect(() => {
    const mapElement = document.getElementById("restaurants-area-map");
    if (!mapAreaEnabled || !mapElement || areaMapRef.current) {
      return;
    }

    const center = selectedAreaCenter || location || { lat: 9.03, lng: 38.74 };
    const map = L.map("restaurants-area-map").setView([center.lat, center.lng], 12);
    areaMapRef.current = map;

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      maxZoom: 19,
      attribution: "&copy; OpenStreetMap contributors",
    }).addTo(map);

    const placeArea = (lat, lng) => {
      setSelectedAreaCenter({ lat, lng });
      if (!areaMarkerRef.current) {
        areaMarkerRef.current = L.marker([lat, lng], { draggable: true, icon: areaMarkerIcon }).addTo(map);
        areaMarkerRef.current.on("dragend", (event) => {
          const point = event.target.getLatLng();
          placeArea(point.lat, point.lng);
        });
      } else {
        areaMarkerRef.current.setLatLng([lat, lng]);
      }

      if (!areaCircleRef.current) {
        areaCircleRef.current = L.circle([lat, lng], {
          radius: radiusKm * 1000,
          color: "#E8B04A",
          fillColor: "#E8B04A",
          fillOpacity: 0.18,
        }).addTo(map);
      } else {
        areaCircleRef.current.setLatLng([lat, lng]);
      }
    };

    placeArea(center.lat, center.lng);
    map.on("click", (event) => placeArea(event.latlng.lat, event.latlng.lng));

    return () => {
      map.remove();
      areaMapRef.current = null;
      areaMarkerRef.current = null;
      areaCircleRef.current = null;
    };
  }, [mapAreaEnabled, location, selectedAreaCenter, areaMarkerIcon]);

  useEffect(() => {
    if (!areaCircleRef.current || !selectedAreaCenter) {
      return;
    }
    areaCircleRef.current.setLatLng([selectedAreaCenter.lat, selectedAreaCenter.lng]);
    areaCircleRef.current.setRadius(radiusKm * 1000);
  }, [radiusKm, selectedAreaCenter]);

  const cityOptions = useMemo(() => {
    return [...new Set(restaurants.map((item) => (item.city || "").trim()).filter(Boolean))].sort((a, b) =>
      a.localeCompare(b),
    );
  }, [restaurants]);

  const activeFilterCount = useMemo(() => {
    return [
      search.trim().length > 0,
      sortBy !== "name_asc",
      deliveryFeeRange !== "all",
      minOrderRange !== "all",
      cityFilter !== "all",
      statusFilter !== "all",
      favoritesOnly,
      freeDeliveryOnly,
    ].filter(Boolean).length;
  }, [
    search,
    sortBy,
    deliveryFeeRange,
    minOrderRange,
    cityFilter,
    statusFilter,
    favoritesOnly,
    freeDeliveryOnly,
  ]);

  const filteredRestaurants = useMemo(() => {
    const normalized = search.trim().toLowerCase();

    const matches = restaurants.filter((restaurant) => {
      const deliveryFee = Number.parseFloat(restaurant.delivery_fee || "0");
      const minimumOrder = Number.parseFloat(restaurant.minimum_order_amount || "0");
      const isOpen = Boolean(restaurant.is_active) && restaurant.status === "approved";

      const matchesSearch =
        !normalized ||
        (restaurant.name || "").toLowerCase().includes(normalized) ||
        (restaurant.city || "").toLowerCase().includes(normalized);

      const matchesDeliveryFee =
        deliveryFeeRange === "all" ||
        (deliveryFeeRange === "free" && deliveryFee <= 0) ||
        (deliveryFeeRange === "low" && deliveryFee > 0 && deliveryFee <= 2) ||
        (deliveryFeeRange === "medium" && deliveryFee > 2 && deliveryFee <= 4) ||
        (deliveryFeeRange === "high" && deliveryFee > 4);

      const matchesMinOrder =
        minOrderRange === "all" ||
        (minOrderRange === "under10" && minimumOrder < 10) ||
        (minOrderRange === "10to20" && minimumOrder >= 10 && minimumOrder <= 20) ||
        (minOrderRange === "20plus" && minimumOrder > 20);

      const matchesCity = cityFilter === "all" || (restaurant.city || "").trim() === cityFilter;
      const matchesStatus =
        statusFilter === "all" || (statusFilter === "open" && isOpen) || (statusFilter === "closed" && !isOpen);
      const matchesFavorites = !favoritesOnly || favorites.includes(restaurant.slug);
      const matchesFreeDelivery = !freeDeliveryOnly || deliveryFee <= 0;
      const restaurantLat = parseCoordinate(restaurant.latitude);
      const restaurantLng = parseCoordinate(restaurant.longitude);
      const distanceToAreaCenter =
        mapAreaEnabled && selectedAreaCenter
          ? haversineDistanceKm(
              { lat: selectedAreaCenter.lat, lng: selectedAreaCenter.lng },
              { lat: restaurantLat, lng: restaurantLng },
            )
          : null;
      const matchesArea =
        !mapAreaEnabled ||
        (distanceToAreaCenter !== null && Number.isFinite(distanceToAreaCenter) && distanceToAreaCenter <= radiusKm);

      return (
        matchesSearch &&
        matchesDeliveryFee &&
        matchesMinOrder &&
        matchesCity &&
        matchesStatus &&
        matchesFavorites &&
        matchesFreeDelivery &&
        matchesArea
      );
    });

    return [...matches].sort((a, b) => {
      if (sortBy === "distance_asc" && location) {
        const aDistance = haversineDistanceKm(
          { lat: location.lat, lng: location.lng },
          { lat: parseCoordinate(a.latitude), lng: parseCoordinate(a.longitude) },
        );
        const bDistance = haversineDistanceKm(
          { lat: location.lat, lng: location.lng },
          { lat: parseCoordinate(b.latitude), lng: parseCoordinate(b.longitude) },
        );
        if (aDistance === null && bDistance === null) return 0;
        if (aDistance === null) return 1;
        if (bDistance === null) return -1;
        return aDistance - bDistance;
      }
      if (sortBy === "name_desc") {
        return (b.name || "").localeCompare(a.name || "");
      }
      if (sortBy === "fee_asc") {
        return Number.parseFloat(a.delivery_fee || "0") - Number.parseFloat(b.delivery_fee || "0");
      }
      if (sortBy === "fee_desc") {
        return Number.parseFloat(b.delivery_fee || "0") - Number.parseFloat(a.delivery_fee || "0");
      }
      if (sortBy === "min_order_asc") {
        return Number.parseFloat(a.minimum_order_amount || "0") - Number.parseFloat(b.minimum_order_amount || "0");
      }
      if (sortBy === "min_order_desc") {
        return Number.parseFloat(b.minimum_order_amount || "0") - Number.parseFloat(a.minimum_order_amount || "0");
      }
      return (a.name || "").localeCompare(b.name || "");
    });
  }, [
    search,
    sortBy,
    deliveryFeeRange,
    minOrderRange,
    cityFilter,
    statusFilter,
    favoritesOnly,
    freeDeliveryOnly,
    favorites,
    restaurants,
    mapAreaEnabled,
    selectedAreaCenter,
    radiusKm,
    location,
  ]);

  const resetFilters = () => {
    setSearch("");
    setSortBy("name_asc");
    setDeliveryFeeRange("all");
    setMinOrderRange("all");
    setCityFilter("all");
    setStatusFilter("all");
    setFavoritesOnly(false);
    setFreeDeliveryOnly(false);
    setMapAreaEnabled(false);
    setRadiusKm(5);
    setPage(1);
  };

  const toggleFavorite = (slug) => {
    setFavorites((prev) => (prev.includes(slug) ? prev.filter((item) => item !== slug) : [...prev, slug]));
  };

  const setFilterAndResetPage = (setter) => (value) => {
    setter(value);
    setPage(1);
  };

  return (
    <PageScaffold title="Restaurants" subtitle="Search, filter, and discover nearby places.">
      <section className="space-y-3 rounded-2xl border border-[#E8B04A]/25 bg-[#F2E6D8] p-4 text-[#333333] shadow-[0_8px_20px_rgba(51,51,51,0.06)]">
        <div className="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-[#E8B04A]/25 bg-[#FFF8F0] px-3 py-2">
          <p className="text-sm">
            Delivery location: <span className="font-medium">{location?.label || "Not set"}</span>
          </p>
          <button
            type="button"
            onClick={openPicker}
            className="min-h-11 rounded-xl border border-[#E8B04A]/35 bg-[#FFF8F0] px-3 py-2 text-sm"
          >
            Change location
          </button>
        </div>

        <div className="grid grid-cols-1 gap-3 md:grid-cols-4">
          <input
            placeholder="Search by name"
            value={search}
            onChange={(event) => setFilterAndResetPage(setSearch)(event.target.value)}
            className="min-h-11 rounded-xl border border-[#E8B04A]/35 bg-[#FFF8F0] px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-[#E8B04A]/40 md:col-span-2"
          />
          <select
            value={sortBy}
            onChange={(event) => setFilterAndResetPage(setSortBy)(event.target.value)}
            className="min-h-11 rounded-xl border border-[#E8B04A]/35 bg-[#FFF8F0] px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-[#E8B04A]/40"
          >
            <option value="name_asc">Name: A-Z</option>
            <option value="name_desc">Name: Z-A</option>
            <option value="fee_asc">Delivery fee: Low to high</option>
            <option value="fee_desc">Delivery fee: High to low</option>
            <option value="min_order_asc">Min order: Low to high</option>
            <option value="min_order_desc">Min order: High to low</option>
            <option value="distance_asc">Distance: Nearest first</option>
          </select>
          <button
            type="button"
            onClick={() => setShowFilters((prev) => !prev)}
            className="min-h-11 rounded-xl border border-[#E8B04A]/35 bg-[#FFF8F0] px-4 py-2.5 text-sm font-medium"
          >
            Filters {activeFilterCount > 0 ? `(${activeFilterCount})` : ""}
          </button>
          <button
            type="button"
            onClick={() => setDrawerOpen(true)}
            className="min-h-11 rounded-xl border border-[#E8B04A]/35 bg-[#FFF8F0] px-4 py-2.5 text-sm font-medium md:hidden"
          >
            Open drawer
          </button>
        </div>

        {showFilters && (
          <div className="grid grid-cols-1 gap-3 border-t border-[#E8B04A]/25 pt-3 md:grid-cols-2 lg:grid-cols-4">
            <select
              value={deliveryFeeRange}
              onChange={(event) => setFilterAndResetPage(setDeliveryFeeRange)(event.target.value)}
              className="min-h-11 rounded-xl border border-[#E8B04A]/35 bg-[#FFF8F0] px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-[#E8B04A]/40"
            >
              <option value="all">All delivery fees</option>
              <option value="free">Free delivery</option>
              <option value="low">Low fee (up to $2)</option>
              <option value="medium">Medium fee ($2-$4)</option>
              <option value="high">High fee (above $4)</option>
            </select>
            <select
              value={minOrderRange}
              onChange={(event) => setFilterAndResetPage(setMinOrderRange)(event.target.value)}
              className="min-h-11 rounded-xl border border-[#E8B04A]/35 bg-[#FFF8F0] px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-[#E8B04A]/40"
            >
              <option value="all">All minimum orders</option>
              <option value="under10">Under $10</option>
              <option value="10to20">$10 - $20</option>
              <option value="20plus">Above $20</option>
            </select>
            <select
              value={cityFilter}
              onChange={(event) => setFilterAndResetPage(setCityFilter)(event.target.value)}
              className="min-h-11 rounded-xl border border-[#E8B04A]/35 bg-[#FFF8F0] px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-[#E8B04A]/40"
            >
              <option value="all">All cities</option>
              {cityOptions.map((city) => (
                <option key={city} value={city}>
                  {city}
                </option>
              ))}
            </select>
            <select
              value={statusFilter}
              onChange={(event) => setFilterAndResetPage(setStatusFilter)(event.target.value)}
              className="min-h-11 rounded-xl border border-[#E8B04A]/35 bg-[#FFF8F0] px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-[#E8B04A]/40"
            >
              <option value="all">All availability</option>
              <option value="open">Open now</option>
              <option value="closed">Closed</option>
            </select>
            <label className="flex min-h-11 items-center gap-2 rounded-xl border border-[#E8B04A]/25 bg-[#FFF8F0] px-3 text-sm">
              <input
                type="checkbox"
                checked={favoritesOnly}
                onChange={(event) => {
                  setFavoritesOnly(event.target.checked);
                  setPage(1);
                }}
                className="h-4 w-4 rounded border-[#E8B04A]/60 text-[#E8B04A] focus:ring-[#E8B04A]/40"
              />
              Favorites only
            </label>
            <label className="flex min-h-11 items-center gap-2 rounded-xl border border-[#E8B04A]/25 bg-[#FFF8F0] px-3 text-sm">
              <input
                type="checkbox"
                checked={freeDeliveryOnly}
                onChange={(event) => {
                  setFreeDeliveryOnly(event.target.checked);
                  setPage(1);
                }}
                className="h-4 w-4 rounded border-[#E8B04A]/60 text-[#E8B04A] focus:ring-[#E8B04A]/40"
              />
              Free delivery only
            </label>
            <label className="flex min-h-11 items-center gap-2 rounded-xl border border-[#E8B04A]/25 bg-[#FFF8F0] px-3 text-sm">
              <input
                type="checkbox"
                checked={mapAreaEnabled}
                onChange={(event) => {
                  setMapAreaEnabled(event.target.checked);
                  setPage(1);
                }}
                className="h-4 w-4 rounded border-[#E8B04A]/60 text-[#E8B04A] focus:ring-[#E8B04A]/40"
              />
              Filter by map area
            </label>
            {mapAreaEnabled ? (
              <div className="rounded-xl border border-[#E8B04A]/25 bg-[#FFF8F0] px-4 py-2.5 text-sm">
                <label htmlFor="radiusKm" className="mb-1 block">
                  Radius: {radiusKm} km
                </label>
                <input
                  id="radiusKm"
                  type="range"
                  min="1"
                  max="30"
                  step="1"
                  value={radiusKm}
                  onChange={(event) => setRadiusKm(Number.parseInt(event.target.value, 10))}
                  className="w-full"
                />
              </div>
            ) : null}
            <div className="rounded-xl border border-[#E8B04A]/25 bg-[#FFF8F0] px-4 py-2.5 text-sm text-[#333333]/75">
              Live data from backend API • {filteredRestaurants.length} results on this page
            </div>
            <div className="md:col-span-2 lg:col-span-4">
              <button
                type="button"
                onClick={resetFilters}
                className="min-h-11 rounded-xl border border-[#E8B04A]/35 bg-[#FFF8F0] px-4 py-2 text-sm"
              >
                Reset filters
              </button>
            </div>
          </div>
        )}
      </section>
      {mapAreaEnabled ? (
        <section className="mt-4 overflow-hidden rounded-2xl border border-[#E8B04A]/25 bg-[#FFF8F0]">
          <div id="restaurants-area-map" className="h-72 w-full" />
          <div className="border-t border-[#E8B04A]/20 px-3 py-2 text-xs text-[#333333]/70">
            Click or drag marker to select area center.
          </div>
        </section>
      ) : null}
      <Drawer open={drawerOpen} title="Filter restaurants" onClose={() => setDrawerOpen(false)}>
        <div className="space-y-3">
          <select
            value={sortBy}
            onChange={(event) => setFilterAndResetPage(setSortBy)(event.target.value)}
            className="min-h-11 w-full rounded-xl border border-[#E8B04A]/35 bg-[#FFF8F0] px-4 py-2.5 text-sm"
          >
            <option value="name_asc">Name: A-Z</option>
            <option value="name_desc">Name: Z-A</option>
            <option value="fee_asc">Delivery fee: Low to high</option>
            <option value="fee_desc">Delivery fee: High to low</option>
            <option value="min_order_asc">Min order: Low to high</option>
            <option value="min_order_desc">Min order: High to low</option>
            <option value="distance_asc">Distance: Nearest first</option>
          </select>
          <select
            value={deliveryFeeRange}
            onChange={(event) => setFilterAndResetPage(setDeliveryFeeRange)(event.target.value)}
            className="min-h-11 w-full rounded-xl border border-[#E8B04A]/35 bg-[#FFF8F0] px-4 py-2.5 text-sm"
          >
            <option value="all">All delivery fees</option>
            <option value="free">Free delivery</option>
            <option value="low">Low fee (up to $2)</option>
            <option value="medium">Medium fee ($2-$4)</option>
            <option value="high">High fee (above $4)</option>
          </select>
          <select
            value={minOrderRange}
            onChange={(event) => setFilterAndResetPage(setMinOrderRange)(event.target.value)}
            className="min-h-11 w-full rounded-xl border border-[#E8B04A]/35 bg-[#FFF8F0] px-4 py-2.5 text-sm"
          >
            <option value="all">All minimum orders</option>
            <option value="under10">Under $10</option>
            <option value="10to20">$10 - $20</option>
            <option value="20plus">Above $20</option>
          </select>
          <select
            value={cityFilter}
            onChange={(event) => setFilterAndResetPage(setCityFilter)(event.target.value)}
            className="min-h-11 w-full rounded-xl border border-[#E8B04A]/35 bg-[#FFF8F0] px-4 py-2.5 text-sm"
          >
            <option value="all">All cities</option>
            {cityOptions.map((city) => (
              <option key={city} value={city}>
                {city}
              </option>
            ))}
          </select>
          <select
            value={statusFilter}
            onChange={(event) => setFilterAndResetPage(setStatusFilter)(event.target.value)}
            className="min-h-11 w-full rounded-xl border border-[#E8B04A]/35 bg-[#FFF8F0] px-4 py-2.5 text-sm"
          >
            <option value="all">All availability</option>
            <option value="open">Open now</option>
            <option value="closed">Closed</option>
          </select>
          <label className="flex min-h-11 items-center gap-2 rounded-xl border border-[#E8B04A]/25 bg-[#FFF8F0] px-3 text-sm">
            <input
              type="checkbox"
              checked={favoritesOnly}
              onChange={(event) => {
                setFavoritesOnly(event.target.checked);
                setPage(1);
              }}
              className="h-4 w-4 rounded border-[#E8B04A]/60 text-[#E8B04A] focus:ring-[#E8B04A]/40"
            />
            Favorites only
          </label>
          <label className="flex min-h-11 items-center gap-2 rounded-xl border border-[#E8B04A]/25 bg-[#FFF8F0] px-3 text-sm">
            <input
              type="checkbox"
              checked={freeDeliveryOnly}
              onChange={(event) => {
                setFreeDeliveryOnly(event.target.checked);
                setPage(1);
              }}
              className="h-4 w-4 rounded border-[#E8B04A]/60 text-[#E8B04A] focus:ring-[#E8B04A]/40"
            />
            Free delivery only
          </label>
          <p className="text-xs text-[#333333]/75">{filteredRestaurants.length} results on this page</p>
          <button
            type="button"
            onClick={() => setDrawerOpen(false)}
            className="min-h-11 w-full rounded-xl bg-[#E8B04A] px-4 py-2 text-sm font-semibold text-[#333333]"
          >
            Apply filters
          </button>
          <button
            type="button"
            onClick={() => {
              resetFilters();
              setDrawerOpen(false);
            }}
            className="min-h-11 w-full rounded-xl border border-[#E8B04A]/35 bg-[#FFF8F0] px-4 py-2 text-sm"
          >
            Reset
          </button>
        </div>
      </Drawer>

      {isLoading ? (
        <section className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
          {Array.from({ length: 6 }).map((_, index) => (
            <Card key={index}>
              <Skeleton className="h-32 w-full rounded-lg" />
              <Skeleton className="mt-3 h-4 w-2/3" />
              <Skeleton className="mt-2 h-3 w-full" />
              <Skeleton className="mt-2 h-3 w-1/2" />
            </Card>
          ))}
        </section>
      ) : isError ? (
        <section className="rounded-2xl border border-[#D96C4A]/35 bg-[#FFF8F0] p-8 text-center text-[#D96C4A] shadow-[0_8px_20px_rgba(51,51,51,0.06)]">
          <h2 className="text-lg font-semibold">Could not load restaurants</h2>
          <p className="mt-2 text-sm">Something went wrong while fetching restaurant data.</p>
          <button
            type="button"
            onClick={() => setIsError(false)}
            className="mt-4 min-h-11 rounded-xl border border-[#D96C4A]/35 bg-[#FFF8F0] px-4 py-2 text-sm text-[#D96C4A]"
          >
            Retry
          </button>
        </section>
      ) : filteredRestaurants.length === 0 ? (
        <section className="rounded-2xl border border-dashed border-[#E8B04A]/45 bg-[#F2E6D8] p-8 text-center text-[#333333] shadow-[0_8px_20px_rgba(51,51,51,0.06)]">
          <h2 className="text-lg font-semibold">No restaurants found</h2>
          <p className="mt-2 text-sm text-[#333333]/75">
            Try changing search text or clearing filters to see more results.
          </p>
          <button
            type="button"
            onClick={resetFilters}
            className="mt-4 min-h-11 rounded-xl bg-[#E8B04A] px-4 py-2 text-sm font-semibold text-[#333333] hover:brightness-95"
          >
            Reset filters
          </button>
        </section>
      ) : (
        <>
          <section className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            {filteredRestaurants.map((restaurant) => (
              // Show distance badge when user location and restaurant coordinates are available.
              <RestaurantCard
                key={restaurant.slug}
                href={`/restaurants/${restaurant.slug}`}
                name={restaurant.name}
                image={toMediaUrl(restaurant.images?.[0]?.image_path) || "https://picsum.photos/id/292/720/420"}
                rating="4.6"
                deliveryTime={restaurant.city || "City not specified"}
                category={`Min ${toCurrency(restaurant.minimum_order_amount)} • ${restaurant.status || "active"}`}
                deliveryFee={toCurrency(restaurant.delivery_fee)}
                discountLabel="Popular"
                distanceKm={toRestaurantDistance(restaurant, location)}
                isFavorite={favorites.includes(restaurant.slug)}
                onToggleFavorite={() => toggleFavorite(restaurant.slug)}
              />
            ))}
          </section>

          <section className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-[#E8B04A]/25 bg-[#F2E6D8] px-4 py-3 text-[#333333] shadow-[0_8px_20px_rgba(51,51,51,0.06)]">
            <p className="text-sm text-[#333333]/75">
              Page {meta.currentPage} of {meta.lastPage} • {filteredRestaurants.length} shown
            </p>
            <div className="flex flex-wrap gap-2">
              <button
                type="button"
                onClick={() => setPage((prev) => Math.max(1, prev - 1))}
                disabled={meta.currentPage <= 1}
                className="min-h-11 rounded-xl border border-[#E8B04A]/35 bg-[#FFF8F0] px-3 py-1.5 text-sm disabled:opacity-50"
              >
                Previous
              </button>
              <button
                type="button"
                onClick={() => setPage((prev) => Math.min(meta.lastPage, prev + 1))}
                disabled={meta.currentPage >= meta.lastPage}
                className="min-h-11 rounded-xl border border-[#E8B04A]/35 bg-[#FFF8F0] px-3 py-1.5 text-sm disabled:opacity-50"
              >
                Next
              </button>
              <button
                type="button"
                onClick={() => {
                  setIsError(false);
                  setIsLoading(true);
                  window.setTimeout(() => setIsLoading(false), 600);
                }}
                className="min-h-11 rounded-xl border border-[#E8B04A]/35 bg-[#FFF8F0] px-3 py-1.5 text-sm"
              >
                Show skeleton
              </button>
              <button
                type="button"
                onClick={() => {
                  setIsLoading(false);
                  setIsError(true);
                }}
                className="min-h-11 rounded-xl border border-[#D96C4A]/35 bg-[#FFF8F0] px-3 py-1.5 text-sm text-[#D96C4A]"
              >
                Simulate error
              </button>
            </div>
          </section>
        </>
      )}
    </PageScaffold>
  );
}
