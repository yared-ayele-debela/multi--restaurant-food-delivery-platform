import { Link } from "react-router-dom";
import { Camera, Coffee, Headset, Leaf, ShieldCheck } from "lucide-react";

const navLinks = [
  { to: "/", label: "Home" },
  { to: "/restaurants", label: "Restaurants" },
  { to: "/restaurants?category=all", label: "Categories" },
];

const companyLinks = [
  { to: "/account", label: "About" },
  { to: "/account", label: "Contact" },
  { to: "/restaurants", label: "Blog" },
];

const supportLinks = [
  { to: "/account", label: "Help Center" },
  { to: "/account", label: "Terms" },
  { to: "/account", label: "Privacy Policy" },
];

export default function WarmFooter({ settings = {} }) {
  const siteName = settings.site_name || "Food Delivery";
  const siteDescription =
    settings.site_description || "Bringing families back to the table with simple, delicious meals.";
  const footerText = settings.footer_text || `© ${new Date().getFullYear()} ${siteName}. All rights reserved.`;
  const contactEmail = settings.contact_email || "";
  const contactPhone = settings.contact_phone || "";
  const socialLinks = [
    { label: "Instagram", icon: <Camera size={16} />, href: settings.instagram_url },
    { label: "Facebook", icon: <Headset size={16} />, href: settings.facebook_url },
    { label: "X", icon: <Leaf size={16} />, href: settings.twitter_url },
    { label: "LinkedIn", icon: <ShieldCheck size={16} />, href: settings.linkedin_url },
    { label: "YouTube", icon: <Coffee size={16} />, href: settings.youtube_url },
  ].filter((item) => Boolean(item.href));

  return (
    <footer className="mt-10 bg-[#F2E6D8] text-[#333333]">
      <div className="mx-auto w-full max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 gap-8 lg:grid-cols-5">
          <div className="space-y-3 lg:col-span-2">
            <p className="text-xl font-semibold">{siteName}</p>
            <p className="max-w-md text-sm text-[#333333]/80">
              {siteDescription}
            </p>
            {contactEmail || contactPhone ? (
              <div className="space-y-1 text-sm text-[#333333]/80">
                {contactEmail ? <p>Email: {contactEmail}</p> : null}
                {contactPhone ? <p>Phone: {contactPhone}</p> : null}
              </div>
            ) : null}
          </div>

          <div>
            <h3 className="text-sm font-semibold uppercase tracking-wide text-[#333333]/80">Navigation</h3>
            <ul className="mt-3 space-y-2 text-sm">
              {navLinks.map((item) => (
                <li key={item.label}>
                  <Link to={item.to} className="text-[#333333]/80 transition hover:text-[#E8B04A]">
                    {item.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          <div>
            <h3 className="text-sm font-semibold uppercase tracking-wide text-[#333333]/80">Company</h3>
            <ul className="mt-3 space-y-2 text-sm">
              {companyLinks.map((item) => (
                <li key={item.label}>
                  <Link to={item.to} className="text-[#333333]/80 transition hover:text-[#E8B04A]">
                    {item.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          <div>
            <h3 className="text-sm font-semibold uppercase tracking-wide text-[#333333]/80">Support</h3>
            <ul className="mt-3 space-y-2 text-sm">
              {supportLinks.map((item) => (
                <li key={item.label}>
                  <Link to={item.to} className="text-[#333333]/80 transition hover:text-[#E8B04A]">
                    {item.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>
        </div>

        <div className="mt-8 grid grid-cols-1 gap-4 border-t border-[#E8B04A]/25 pt-6 md:grid-cols-[1fr_auto] md:items-center">
          <div className="max-w-md">
            <p className="text-sm font-medium text-[#333333]">Newsletter</p>
            <div className="mt-2 flex flex-col gap-2 sm:flex-row">
              <input
                type="email"
                placeholder="Your email address"
                className="min-h-11 w-full rounded-xl border border-[#E8B04A]/30 bg-[#FFF8F0] px-4 text-sm text-[#333333] outline-none focus:ring-2 focus:ring-[#E8B04A]/40"
              />
              <button
                type="button"
                className="inline-flex min-h-11 items-center justify-center rounded-xl bg-[#E8B04A] px-5 text-sm font-semibold text-[#333333] shadow-sm transition duration-200 hover:scale-[1.02] active:scale-95"
              >
                Subscribe
              </button>
            </div>
          </div>

          <div className="flex items-center gap-2">
            {socialLinks.map((social) => (
              <a
                key={social.label}
                href={social.href}
                target="_blank"
                rel="noreferrer"
                aria-label={social.label}
                className="inline-flex min-h-11 min-w-11 items-center justify-center rounded-full bg-[#FFF8F0] text-sm text-[#333333] shadow-sm transition duration-200 hover:scale-105 hover:text-[#E8B04A]"
              >
                {social.icon}
              </a>
            ))}
          </div>
        </div>

        <div className="mt-5 border-t border-[#E8B04A]/20 pt-4 text-xs text-[#333333]/70">
          {footerText}
        </div>
      </div>
    </footer>
  );
}
