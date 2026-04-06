export function parseCoordinate(value) {
  const number = Number.parseFloat(value);
  return Number.isFinite(number) ? number : null;
}

export function hasValidCoordinates(lat, lng) {
  return Number.isFinite(lat) && Number.isFinite(lng);
}

export function haversineDistanceKm(from, to) {
  if (!from || !to) {
    return null;
  }
  const fromLat = Number(from.lat);
  const fromLng = Number(from.lng);
  const toLat = Number(to.lat);
  const toLng = Number(to.lng);
  if (!hasValidCoordinates(fromLat, fromLng) || !hasValidCoordinates(toLat, toLng)) {
    return null;
  }

  const toRad = (value) => (value * Math.PI) / 180;
  const earthRadiusKm = 6371;
  const latDelta = toRad(toLat - fromLat);
  const lngDelta = toRad(toLng - fromLng);
  const latA = toRad(fromLat);
  const latB = toRad(toLat);

  const h =
    Math.sin(latDelta / 2) * Math.sin(latDelta / 2) +
    Math.cos(latA) * Math.cos(latB) * Math.sin(lngDelta / 2) * Math.sin(lngDelta / 2);
  const c = 2 * Math.atan2(Math.sqrt(h), Math.sqrt(1 - h));
  return earthRadiusKm * c;
}
