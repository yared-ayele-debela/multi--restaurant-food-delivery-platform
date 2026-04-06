import { Outlet } from "react-router-dom";
import { useAuth } from "../context/AuthContext";
import { useCartBadge } from "../context/CartBadgeContext";
import { useSiteSettings } from "../context/SiteSettingsContext";
import WarmFooter from "./layout/WarmFooter";
import WarmHeader from "./layout/WarmHeader";
import LocationGate from "./location/LocationGate";

export default function AppShell() {
  const { cartCount } = useCartBadge();
  const { user, logout } = useAuth();
  const { settings } = useSiteSettings();

  return (
    <div className="min-h-screen bg-[#FFF8F0] text-[#333333]">
      <WarmHeader cartCount={cartCount} user={user} onLogout={logout} settings={settings} />
      <LocationGate />

      <main className="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <Outlet />
      </main>

      <WarmFooter settings={settings} />
    </div>
  );
}
