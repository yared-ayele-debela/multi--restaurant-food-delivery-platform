import { useState } from "react";
import { Link, NavLink } from "react-router-dom";
import { MapPin, Menu, Search, ShoppingCart, X } from "lucide-react";

const navItems = [
  { to: "/", label: "Home" },
  { to: "/restaurants", label: "Restaurants" },
  { to: "/account", label: "About" },
];

function navClass({ isActive }) {
  return [
    "rounded-xl px-3 py-2 text-sm font-medium transition duration-200",
    isActive
      ? "text-[#E8B04A] underline decoration-[#E8B04A] underline-offset-4"
      : "text-[#333333]/80 hover:text-[#333333]",
  ].join(" ");
}

export default function WarmHeader({ cartCount, user, onLogout, settings = {} }) {
  const [mobileOpen, setMobileOpen] = useState(false);
  const siteName = settings.site_name || "Food Delivery";
  const logoUrl = settings.logo_url || "";

  return (
    <header className="sticky top-0 z-40 border-b border-[#E8B04A]/25 bg-[#FFF8F0]/95 shadow-[0_6px_16px_rgba(51,51,51,0.05)] backdrop-blur">
      <div className="mx-auto flex h-[76px] w-full max-w-7xl items-center gap-3 px-4 sm:px-6 lg:px-8">
        <button
          type="button"
          className="inline-flex min-h-11 min-w-11 items-center justify-center rounded-xl border border-[#E8B04A]/35 bg-[#F2E6D8] text-[#333333] md:hidden"
          onClick={() => setMobileOpen(true)}
          aria-label="Open navigation menu"
        >
          <Menu size={18} />
        </button>

        <Link to="/" className="shrink-0 text-lg font-semibold tracking-tight text-[#333333]">
          {logoUrl ? (
            <span className="inline-flex items-center gap-2">
              <img src={logoUrl} alt={`${siteName} logo`} className="h-8 w-auto rounded object-contain" />
              <span>{siteName}</span>
            </span>
          ) : (
            siteName
          )}
        </Link>

        <nav className="hidden items-center gap-1 lg:flex">
          {navItems.map((item) => (
            <NavLink key={item.to} to={item.to} className={navClass}>
              {item.label}
            </NavLink>
          ))}
        </nav>

        <div className="ml-auto hidden items-center gap-2 md:flex">
          <div className="relative">
            <span className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[#333333]/60">
              <Search size={16} />
            </span>
            <input
              placeholder="Search food or restaurant"
              className="min-h-11 w-56 rounded-full border border-[#E8B04A]/25 bg-[#F2E6D8] py-2 pl-9 pr-3 text-sm text-[#333333] outline-none placeholder:text-[#333333]/55 focus:ring-2 focus:ring-[#E8B04A]/40"
            />
          </div>

          <div className="relative">
            <span className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[#333333]/60">
              <MapPin size={16} />
            </span>
            <select className="min-h-11 rounded-full border border-[#E8B04A]/25 bg-[#F2E6D8] py-2 pl-9 pr-3 text-sm text-[#333333] outline-none focus:ring-2 focus:ring-[#E8B04A]/40">
              <option>Current location</option>
              <option>Addis Ababa</option>
              <option>Hawassa</option>
              <option>Bahir Dar</option>
            </select>
          </div>
        </div>

        <div className="flex items-center gap-2">
          <Link
            to="/cart"
            className="relative inline-flex min-h-11 min-w-11 items-center justify-center rounded-xl bg-[#F2E6D8] text-[#333333] shadow-sm transition hover:scale-[1.02]"
            aria-label="Open cart"
          >
            <ShoppingCart size={18} />
            <span className="absolute -right-1 -top-1 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-[#D96C4A] px-1 text-[11px] font-semibold text-white">
              {cartCount}
            </span>
          </Link>

          {user ? (
            <>
              <Link
                to="/account"
                className="hidden min-h-11 items-center justify-center rounded-xl bg-[#E8B04A] px-4 text-sm font-semibold text-[#333333] shadow-sm transition hover:scale-[1.02] sm:inline-flex"
              >
                {user.name || "Profile"}
              </Link>
              <button
                type="button"
                onClick={onLogout}
                className="hidden min-h-11 rounded-xl border border-[#E8B04A]/35 bg-[#F2E6D8] px-3 text-sm text-[#333333] sm:inline-flex sm:items-center"
              >
                Logout
              </button>
            </>
          ) : (
            <Link
              to="/login"
              className="hidden min-h-11 items-center justify-center rounded-xl bg-[#E8B04A] px-4 text-sm font-semibold text-[#333333] shadow-sm transition hover:scale-[1.02] sm:inline-flex"
            >
              Login
            </Link>
          )}
        </div>
      </div>

      {mobileOpen ? (
        <div className="fixed inset-0 z-50 md:hidden">
          <button
            type="button"
            className="absolute inset-0 bg-black/20"
            onClick={() => setMobileOpen(false)}
            aria-label="Close menu overlay"
          />
          <aside className="absolute inset-y-0 left-0 w-[86%] max-w-xs overflow-y-auto bg-[#FFF8F0] p-5 shadow-2xl">
            <div className="flex items-center justify-between">
              <p className="text-lg font-semibold text-[#333333]">{siteName}</p>
              <button
                type="button"
                onClick={() => setMobileOpen(false)}
                className="inline-flex min-h-11 min-w-11 items-center justify-center rounded-xl border border-[#E8B04A]/35 bg-[#F2E6D8]"
              >
                <X size={18} />
              </button>
            </div>

            <div className="mt-4 space-y-2">
              <input
                placeholder="Search food or restaurant"
                className="min-h-11 w-full rounded-full border border-[#E8B04A]/25 bg-[#F2E6D8] px-4 text-sm text-[#333333] outline-none"
              />
              <div className="relative">
                <span className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[#333333]/60">
                  <MapPin size={16} />
                </span>
                <select className="min-h-11 w-full rounded-full border border-[#E8B04A]/25 bg-[#F2E6D8] py-2 pl-9 pr-3 text-sm text-[#333333] outline-none">
                  <option>Current location</option>
                  <option>Addis Ababa</option>
                  <option>Hawassa</option>
                </select>
              </div>
            </div>

            <nav className="mt-5 grid gap-1">
              {[...navItems, { to: "/cart", label: `Cart (${cartCount})` }].map((item) => (
                <NavLink
                  key={item.to}
                  to={item.to}
                  onClick={() => setMobileOpen(false)}
                  className={navClass}
                >
                  {item.label}
                </NavLink>
              ))}
            </nav>

            <div className="mt-5 grid gap-2">
              {user ? (
                <>
                  <Link
                    to="/account"
                    onClick={() => setMobileOpen(false)}
                    className="inline-flex min-h-11 items-center justify-center rounded-xl bg-[#E8B04A] px-4 text-sm font-semibold text-[#333333]"
                  >
                    Profile
                  </Link>
                  <button
                    type="button"
                    onClick={() => {
                      onLogout?.();
                      setMobileOpen(false);
                    }}
                    className="inline-flex min-h-11 items-center justify-center rounded-xl border border-[#E8B04A]/35 bg-[#F2E6D8] text-sm text-[#333333]"
                  >
                    Logout
                  </button>
                </>
              ) : (
                <Link
                  to="/login"
                  onClick={() => setMobileOpen(false)}
                  className="inline-flex min-h-11 items-center justify-center rounded-xl bg-[#E8B04A] px-4 text-sm font-semibold text-[#333333]"
                >
                  Login
                </Link>
              )}
            </div>
          </aside>
        </div>
      ) : null}
    </header>
  );
}
