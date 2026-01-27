import { useParams } from "react-router-dom";
import { useEffect, useMemo, useState } from "react";
import PageScaffold from "../components/PageScaffold";
import { fetchRestaurant, toMediaUrl } from "../api/catalogApi";
import ProductCard from "../components/cards/ProductCard";
import { useCartBadge } from "../context/CartBadgeContext";
import { Toast } from "../components/ui/primitives";

function toCurrency(value) {
  const amount = Number.parseFloat(value || "0");
  return `$${amount.toFixed(2)}`;
}

export default function RestaurantDetailPage() {
  const { slug } = useParams();
  const { addLine, replaceCartWithLine, cartRestaurantSlug } = useCartBadge();
  const [branchId, setBranchId] = useState("");
  const [restaurant, setRestaurant] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [toast, setToast] = useState("");

  useEffect(() => {
    let mounted = true;
    async function loadRestaurant() {
      try {
        setLoading(true);
        setError("");
        const data = await fetchRestaurant(slug, branchId || undefined);
        if (!mounted) {
          return;
        }
        setRestaurant(data);
        if (!branchId && data?.branches?.[0]?.id) {
          setBranchId(String(data.branches[0].id));
        }
      } catch {
        if (mounted) {
          setError("Failed to load restaurant from backend.");
        }
      } finally {
        if (mounted) {
          setLoading(false);
        }
      }
    }
    if (slug) {
      loadRestaurant();
    }
    return () => {
      mounted = false;
    };
  }, [slug, branchId]);

  const menuCategories = useMemo(() => restaurant?.categories || [], [restaurant]);
  const isOpen = Boolean(restaurant?.is_active) && restaurant?.status === "approved";
  const coverImage = toMediaUrl(restaurant?.images?.[0]?.image_path) || "https://picsum.photos/id/431/1280/480";
  const logoImage = toMediaUrl(restaurant?.images?.[1]?.image_path || restaurant?.images?.[0]?.image_path) || "https://picsum.photos/id/1012/120/120";

  const handleQuickAdd = (item) => {
    if (!isOpen) {
      return;
    }

    const sizes = Array.isArray(item?.sizes) ? item.sizes : [];
    const defaultSize = sizes.find((size) => size?.is_default) || sizes[0];
    const unitPrice = Number.parseFloat(
      defaultSize?.price ?? item?.discount_price ?? item?.base_price ?? "0",
    );

    const line = {
      id: `${Date.now()}-${Math.random().toString(16).slice(2, 7)}`,
      productId: String(item.id),
      name: item.name || "Menu item",
      image: toMediaUrl(item.image) || "https://picsum.photos/id/292/240/180",
      sizeLabel: defaultSize?.name || "Regular",
      addons: [],
      quantity: 1,
      unitTotal: Number.isFinite(unitPrice) ? unitPrice : 0,
      signature: JSON.stringify({
        productId: String(item.id),
        size: defaultSize ? String(defaultSize.id) : "regular",
        addons: [],
      }),
    };

    if (cartRestaurantSlug && cartRestaurantSlug !== slug) {
      const confirmed = window.confirm(
        "Your cart has items from another restaurant. Clear cart and add this item instead?",
      );
      if (!confirmed) {
        return;
      }
      replaceCartWithLine({ restaurantSlug: slug, line });
    } else {
      addLine({ restaurantSlug: slug, line });
    }

    setToast(`${item.name} added to cart.`);
  };

  return (
    <PageScaffold title={restaurant?.name || "Restaurant"} subtitle="Menu and branch details">
      {loading ? (
        <section className="rounded-2xl border border-[#E8B04A]/25 bg-[#F2E6D8] p-4 text-sm text-[#333333]/75 shadow-[0_8px_20px_rgba(51,51,51,0.06)]">
          Loading restaurant...
        </section>
      ) : null}
      {error ? (
        <section className="rounded-2xl border border-[#D96C4A]/35 bg-[#FFF8F0] p-4 text-sm text-[#D96C4A] shadow-[0_8px_20px_rgba(51,51,51,0.06)]">
          {error}
        </section>
      ) : null}
      <section className="overflow-hidden rounded-2xl border border-[#E8B04A]/25 bg-[#F2E6D8] text-[#333333] shadow-[0_8px_20px_rgba(51,51,51,0.06)]">
        <img
          src={coverImage}
          srcSet={`${coverImage} 1280w`}
          sizes="(max-width: 1024px) 100vw, 70vw"
          width="1280"
          height="480"
          loading="eager"
          alt={`${restaurant?.name || "Restaurant"} cover`}
          className="h-44 w-full object-cover sm:h-56"
        />
        <div className="space-y-4 p-5">
          <div className="flex flex-wrap items-center gap-3">
            <img
              src={logoImage}
              width="72"
              height="72"
              alt={`${restaurant?.name || "Restaurant"} logo`}
              className="h-16 w-16 rounded-full border border-[#E8B04A]/30 object-cover"
            />
            <div>
              <h2 className="text-xl font-semibold">{restaurant?.name}</h2>
              <p className="text-sm text-[#333333]/75">
                {restaurant?.city || "City"} • Delivery {toCurrency(restaurant?.delivery_fee)} • Minimum {toCurrency(restaurant?.minimum_order_amount)}
              </p>
            </div>
            <span
              className={`ml-auto rounded-full px-3 py-1 text-xs font-medium ${
                isOpen
                  ? "bg-[#7A9E7E]/20 text-[#7A9E7E]"
                  : "bg-[#D96C4A]/20 text-[#D96C4A]"
              }`}
            >
              {isOpen ? "Open now" : "Closed"}
            </span>
          </div>

          <div className="grid grid-cols-1 gap-3 sm:grid-cols-[1fr_auto] sm:items-center">
            <p className="text-sm text-[#333333]/75">
              {isOpen ? "Accepting orders now." : "Currently unavailable for new orders."}
            </p>
            <label className="flex items-center gap-2 text-sm">
              <span>Branch</span>
              <select
                value={branchId}
                onChange={(event) => setBranchId(event.target.value)}
                className="min-h-11 rounded-xl border border-[#E8B04A]/35 bg-[#FFF8F0] px-3 py-2 outline-none focus:ring-2 focus:ring-[#E8B04A]/40"
              >
                {(restaurant?.branches || []).map((branch) => (
                  <option key={branch.id} value={String(branch.id)}>
                    {branch.name}
                  </option>
                ))}
              </select>
            </label>
          </div>
        </div>
      </section>

      <section className="sticky top-[73px] z-20 rounded-2xl border border-[#E8B04A]/25 bg-[#FFF8F0]/95 p-3 shadow-[0_8px_20px_rgba(51,51,51,0.06)] backdrop-blur">
        <nav className="flex flex-wrap gap-2">
          {menuCategories.map((category) => (
            <a
              key={category.slug || category.id}
              href={`#${category.slug || category.id}`}
              className="rounded-full border border-[#E8B04A]/35 bg-[#F2E6D8] px-3 py-1.5 text-sm text-[#333333]/80 hover:bg-[#E8B04A]/15 hover:text-[#333333]"
            >
              {category.name}
            </a>
          ))}
        </nav>
      </section>

      <section className="space-y-5">
        {menuCategories.map((category) => (
          <article key={category.slug || category.id} id={category.slug || category.id} className="space-y-3 scroll-mt-32">
            <h2 className="text-lg font-semibold">{category.name}</h2>
            <div className="grid grid-cols-1 gap-3 lg:grid-cols-2">
              {(category.products || []).map((item) => (
                <div
                  key={item.id}
                  className={`${
                    isOpen ? "" : "opacity-70"
                  }`}
                >
                  <ProductCard
                    horizontal
                    name={item.name}
                    description={item.description || "Freshly prepared by the kitchen"}
                    price={toCurrency(item.discount_price || item.base_price)}
                    image={toMediaUrl(item.image) || "https://picsum.photos/id/292/240/180"}
                    onAdd={isOpen ? () => handleQuickAdd(item) : undefined}
                    addHref={isOpen ? `/restaurants/${slug}/p/${item.id}` : undefined}
                    addLabel={isOpen ? "Add" : "Unavailable"}
                    disabled={!isOpen}
                    badge={item.discount_price ? "Offer" : undefined}
                  />
                </div>
              ))}
            </div>
          </article>
        ))}
      </section>

      {!isOpen && (
        <section className="rounded-2xl border border-[#D96C4A]/35 bg-[#FFF8F0] p-4 text-sm text-[#D96C4A] shadow-[0_8px_20px_rgba(51,51,51,0.06)]">
          This restaurant is currently closed.
        </section>
      )}
      {isOpen && (
        <section className="rounded-2xl border border-[#7A9E7E]/35 bg-[#FFF8F0] p-4 text-sm text-[#7A9E7E] shadow-[0_8px_20px_rgba(51,51,51,0.06)]">
          Open now and accepting orders from selected branch.
        </section>
      )}
      <section>
        <div className="text-xs text-[#333333]/70">
          Branch selected: <span className="font-medium">{branchId}</span>
        </div>
      </section>
      <Toast open={Boolean(toast)} message={toast} onDone={() => setToast("")} />
    </PageScaffold>
  );
}
