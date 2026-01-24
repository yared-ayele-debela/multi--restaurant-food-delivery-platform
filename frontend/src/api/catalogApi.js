const RAW_API_URL = import.meta.env.VITE_API_URL || "";
const API_ORIGIN = RAW_API_URL.trim().replace(/\/+$/, "");
const API_BASE_URL = API_ORIGIN ? `${API_ORIGIN}/api/v1` : "/api/v1";
const MEDIA_ORIGIN = API_ORIGIN || "http://127.0.0.1:8000";

function unwrapPayload(json) {
  if (json && typeof json === "object" && "data" in json) {
    return json.data;
  }
  return json;
}

export function toMediaUrl(path) {
  if (!path) {
    return "";
  }
  if (path.startsWith("http://") || path.startsWith("https://")) {
    return path;
  }
  return `${MEDIA_ORIGIN}${path.startsWith("/") ? "" : "/"}${path}`;
}

async function request(path) {
  const response = await fetch(`${API_BASE_URL}${path}`, {
    headers: { Accept: "application/json" },
  });
  if (!response.ok) {
    throw new Error(`Request failed: ${response.status}`);
  }
  return response.json();
}

export async function fetchRestaurants({ page = 1, perPage = 15 } = {}) {
  const json = await request(`/restaurants?page=${page}&per_page=${perPage}`);
  const items = unwrapPayload(json) || [];
  const meta = json?.meta || {};
  return {
    items,
    meta: {
      currentPage: meta.current_page || page,
      lastPage: meta.last_page || 1,
      total: meta.total || items.length,
    },
  };
}

export async function fetchCategories() {
  const json = await request("/categories");
  const items = unwrapPayload(json) || [];
  return Array.isArray(items) ? items : [];
}

export async function fetchSiteSettings() {
  const json = await request("/settings");
  const data = unwrapPayload(json) || {};
  return data && typeof data === "object" ? data : {};
}

export async function fetchRestaurant(slug, branchId) {
  const query = branchId ? `?branch_id=${branchId}` : "";
  const json = await request(`/restaurants/${slug}${query}`);
  return unwrapPayload(json);
}
