import { useEffect, useMemo, useRef, useState } from "react";
import { MapPin, Navigation, Search } from "lucide-react";
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import { useLocationContext } from "../../context/LocationContext";

function fallbackLabel(lat, lng) {
  return `Lat ${lat.toFixed(4)}, Lng ${lng.toFixed(4)}`;
}

export default function LocationGate() {
  const { location, hasLocation, setLocation, pickerOpen, closePicker } = useLocationContext();
  const [search, setSearch] = useState("");
  const [searching, setSearching] = useState(false);
  const [error, setError] = useState("");
  const [selectedPoint, setSelectedPoint] = useState(null);
  const [selectedLabel, setSelectedLabel] = useState("");
  const mapRef = useRef(null);
  const markerRef = useRef(null);
  const pickerIcon = useMemo(
    () =>
      L.divIcon({
        className: "fd-location-picker-marker",
        html: '<div style="width:30px;height:30px;border-radius:999px;background:#E8B04A;border:2px solid #FFF8F0;box-shadow:0 3px 10px rgba(0,0,0,0.2);"></div>',
        iconSize: [30, 30],
        iconAnchor: [15, 15],
      }),
    [],
  );

  const open = pickerOpen || !hasLocation;

  const canConfirmSelected = useMemo(() => {
    return selectedPoint && Number.isFinite(selectedPoint.lat) && Number.isFinite(selectedPoint.lng);
  }, [selectedPoint]);

  useEffect(() => {
    if (!open) {
      return undefined;
    }
    const map = L.map("location-gate-map").setView([9.03, 38.74], 12);
    mapRef.current = map;

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      maxZoom: 19,
      attribution: "&copy; OpenStreetMap contributors",
    }).addTo(map);

    let marker = null;
    if (location?.lat && location?.lng) {
      marker = L.marker([location.lat, location.lng], { icon: pickerIcon }).addTo(map);
      markerRef.current = marker;
      map.setView([location.lat, location.lng], 13);
      setSelectedPoint({ lat: location.lat, lng: location.lng });
      setSelectedLabel(location.label || fallbackLabel(location.lat, location.lng));
    }

    function placeMarker(lat, lng, label = "") {
      if (!marker) {
        marker = L.marker([lat, lng], { icon: pickerIcon }).addTo(map);
      } else {
        marker.setLatLng([lat, lng]);
      }
      markerRef.current = marker;
      setSelectedPoint({ lat, lng });
      setSelectedLabel(label || fallbackLabel(lat, lng));
      map.setView([lat, lng], Math.max(map.getZoom(), 13));
    }

    async function reverseGeocode(lat, lng) {
      const key = import.meta.env.VITE_GEOAPIFY_API_KEY || "";
      if (!key) {
        return "";
      }
      try {
        const url = `https://api.geoapify.com/v1/geocode/reverse?lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}&apiKey=${encodeURIComponent(key)}`;
        const response = await fetch(url);
        if (!response.ok) {
          return "";
        }
        const data = await response.json();
        return data?.features?.[0]?.properties?.formatted || "";
      } catch {
        return "";
      }
    }

    map.on("click", async (event) => {
      const lat = event.latlng.lat;
      const lng = event.latlng.lng;
      const label = (await reverseGeocode(lat, lng)) || fallbackLabel(lat, lng);
      placeMarker(lat, lng, label);
      setError("");
    });

    return () => {
      map.remove();
      mapRef.current = null;
      markerRef.current = null;
    };
  }, [open, location, pickerIcon]);

  const useCurrentLocation = () => {
    setError("");
    if (!navigator.geolocation) {
      setError("Geolocation is not available in your browser.");
      return;
    }
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        setLocation({
          lat,
          lng,
          label: "Current location",
          source: "geolocation",
        });
        closePicker();
      },
      () => {
        setError("Could not access your location. You can select on map.");
      },
      { enableHighAccuracy: true, timeout: 12000 },
    );
  };

  const searchLocation = async () => {
    const text = search.trim();
    const key = import.meta.env.VITE_GEOAPIFY_API_KEY || "";
    if (!text || !key) {
      if (!key) {
        setError("Missing VITE_GEOAPIFY_API_KEY in frontend env.");
      }
      return;
    }
    setSearching(true);
    setError("");
    try {
      const url = `https://api.geoapify.com/v1/geocode/autocomplete?text=${encodeURIComponent(text)}&limit=1&apiKey=${encodeURIComponent(key)}`;
      const response = await fetch(url);
      if (!response.ok) {
        throw new Error("search failed");
      }
      const data = await response.json();
      const feature = data?.features?.[0];
      if (!feature) {
        setError("No location found for your search.");
        return;
      }
      const [lng, lat] = feature.geometry.coordinates;
      setSelectedPoint({ lat, lng });
      setSelectedLabel(feature.properties?.formatted || fallbackLabel(lat, lng));

      if (mapRef.current) {
        mapRef.current.setView([lat, lng], 14);
        if (!markerRef.current) {
          markerRef.current = L.marker([lat, lng], { icon: pickerIcon }).addTo(mapRef.current);
        } else {
          markerRef.current.setLatLng([lat, lng]);
        }
      }
    } catch {
      setError("Failed to search location. Try again.");
    } finally {
      setSearching(false);
    }
  };

  const confirmSelected = () => {
    if (!canConfirmSelected) {
      return;
    }
    setLocation({
      lat: selectedPoint.lat,
      lng: selectedPoint.lng,
      label: selectedLabel || fallbackLabel(selectedPoint.lat, selectedPoint.lng),
      source: "map",
    });
    closePicker();
  };

  if (!open) {
    return null;
  }

  return (
    <div className="fixed inset-0 z-[60] flex items-center justify-center bg-black/45 p-4">
      <div className="w-full max-w-3xl rounded-2xl border border-[#E8B04A]/25 bg-[#FFF8F0] p-5 text-[#333333] shadow-2xl">
        <h3 className="text-lg font-semibold">Choose your delivery location</h3>
        <p className="mt-1 text-sm text-[#333333]/75">
          We will show restaurants near your location. You can use current location or pick from map.
        </p>

        <div className="mt-4 flex flex-wrap gap-2">
          <button
            type="button"
            onClick={useCurrentLocation}
            className="inline-flex min-h-11 items-center gap-2 rounded-xl bg-[#E8B04A] px-4 py-2 text-sm font-semibold text-[#333333]"
          >
            <Navigation size={16} />
            Use current location
          </button>
        </div>

        <div className="mt-3 flex gap-2">
          <input
            value={search}
            onChange={(event) => setSearch(event.target.value)}
            placeholder="Search area, street, or city"
            className="min-h-11 flex-1 rounded-xl border border-[#E8B04A]/35 bg-[#FFF8F0] px-4 py-2.5 text-sm"
          />
          <button
            type="button"
            onClick={searchLocation}
            disabled={searching}
            className="inline-flex min-h-11 items-center gap-2 rounded-xl border border-[#E8B04A]/35 bg-[#F2E6D8] px-4 py-2 text-sm"
          >
            <Search size={16} />
            {searching ? "Searching..." : "Search"}
          </button>
        </div>

        <div className="mt-3 overflow-hidden rounded-xl border border-[#E8B04A]/25">
          <div id="location-gate-map" className="h-72 w-full" />
        </div>

        {selectedPoint ? (
          <div className="mt-3 rounded-xl border border-[#E8B04A]/25 bg-[#F2E6D8] px-3 py-2 text-sm">
            <p className="font-medium">Selected</p>
            <p className="text-[#333333]/75">{selectedLabel || fallbackLabel(selectedPoint.lat, selectedPoint.lng)}</p>
            <p className="text-xs text-[#333333]/70">
              {selectedPoint.lat.toFixed(6)}, {selectedPoint.lng.toFixed(6)}
            </p>
          </div>
        ) : null}

        {error ? <p className="mt-3 text-sm text-[#D96C4A]">{error}</p> : null}

        <div className="mt-4 flex flex-wrap justify-end gap-2">
          <button
            type="button"
            onClick={confirmSelected}
            disabled={!canConfirmSelected}
            className="inline-flex min-h-11 items-center gap-2 rounded-xl bg-[#E8B04A] px-4 py-2 text-sm font-semibold text-[#333333] disabled:opacity-50"
          >
            <MapPin size={16} />
            Confirm location
          </button>
        </div>
      </div>
    </div>
  );
}
