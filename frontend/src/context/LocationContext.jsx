import { createContext, useContext, useEffect, useMemo, useState } from "react";

const STORAGE_KEY = "fd_user_location_v1";

const LocationContext = createContext(null);

function readStoredLocation() {
  if (typeof window === "undefined") {
    return null;
  }
  try {
    const raw = window.localStorage.getItem(STORAGE_KEY);
    if (!raw) {
      return null;
    }
    const parsed = JSON.parse(raw);
    if (!parsed || typeof parsed !== "object") {
      return null;
    }
    const lat = Number.parseFloat(parsed.lat);
    const lng = Number.parseFloat(parsed.lng);
    if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
      return null;
    }
    return {
      lat,
      lng,
      label: parsed.label || "Selected location",
      source: parsed.source || "manual",
    };
  } catch {
    return null;
  }
}

export function LocationProvider({ children }) {
  const [location, setLocationState] = useState(() => readStoredLocation());
  const [pickerOpen, setPickerOpen] = useState(false);

  useEffect(() => {
    if (typeof window === "undefined") {
      return;
    }
    if (!location) {
      window.localStorage.removeItem(STORAGE_KEY);
      return;
    }
    window.localStorage.setItem(STORAGE_KEY, JSON.stringify(location));
  }, [location]);

  const setLocation = (nextLocation) => {
    if (!nextLocation) {
      setLocationState(null);
      return;
    }
    const lat = Number.parseFloat(nextLocation.lat);
    const lng = Number.parseFloat(nextLocation.lng);
    if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
      return;
    }
    setLocationState({
      lat,
      lng,
      label: nextLocation.label || "Selected location",
      source: nextLocation.source || "manual",
    });
  };

  const clearLocation = () => setLocationState(null);
  const openPicker = () => setPickerOpen(true);
  const closePicker = () => setPickerOpen(false);

  const value = useMemo(
    () => ({
      location,
      hasLocation: Boolean(location),
      pickerOpen,
      setLocation,
      clearLocation,
      openPicker,
      closePicker,
    }),
    [location, pickerOpen],
  );

  return <LocationContext.Provider value={value}>{children}</LocationContext.Provider>;
}

export function useLocationContext() {
  const context = useContext(LocationContext);
  if (!context) {
    throw new Error("useLocationContext must be used inside LocationProvider.");
  }
  return context;
}
