import { useEffect, useMemo, useState } from "react";
import { Link } from "react-router-dom";
import { Bike, CakeSlice, Coffee, Headset, Leaf, Pizza, Sandwich, ShieldCheck, Soup, UtensilsCrossed } from "lucide-react";
import PageScaffold from "../components/PageScaffold";
import { fetchCategories, fetchRestaurant, fetchRestaurants, toMediaUrl } from "../api/catalogApi";
import CategoryCard from "../components/cards/CategoryCard";
import ProductCard from "../components/cards/ProductCard";
import RestaurantCard from "../components/cards/RestaurantCard";
import { useLocationContext } from "../context/LocationContext";
import { haversineDistanceKm, parseCoordinate } from "../utils/location";

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

function categoryIcon(name) {
  const lower = (name || "").toLowerCase();
  const iconClass = "h-5 w-5";
  if (lower.includes("pizza")) return <Pizza className={iconClass} />;
  if (lower.includes("burger")) return <Sandwich className={iconClass} />;
  if (lower.includes("asian")) return <Soup className={iconClass} />;
  if (lower.includes("dessert")) return <CakeSlice className={iconClass} />;
  if (lower.includes("breakfast")) return <Coffee className={iconClass} />;
  if (lower.includes("dinner")) return <UtensilsCrossed className={iconClass} />;
  return <Leaf className={iconClass} />;
}

export default function HomePage() {
  const { location } = useLocationContext();
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [restaurants, setRestaurants] = useState([]);
  const [categories, setCategories] = useState([]);
  const [discountFoods, setDiscountFoods] = useState([]);

  useEffect(() => {
    let mounted = true;

    async function loadData() {
      try {
        setLoading(true);
        setError("");
        const [list, categoryItems] = await Promise.all([
          fetchRestaurants({ page: 1, perPage: 12 }),
          fetchCategories(),
        ]);
        if (!mounted) {
          return;
        }
        setRestaurants(list.items || []);
        setCategories((categoryItems || []).slice(0, 12));

        const detailTargets = (list.items || []).slice(0, 6);
        const details = await Promise.all(
          detailTargets.map((restaurant) => fetchRestaurant(restaurant.slug).catch(() => null)),
        );
        if (!mounted) {
          return;
        }

        const deals = [];

        details.filter(Boolean).forEach((restaurant) => {
          (restaurant.categories || []).forEach((category) => {
            (category.products || []).forEach((product) => {
              const basePrice = Number.parseFloat(product.base_price || "0");
              const discountPrice = Number.parseFloat(product.discount_price || "0");
              if (discountPrice > 0 && discountPrice < basePrice) {
                deals.push({
                  id: `${restaurant.slug}-${product.id}`,
                  title: product.name,
                  restaurant: restaurant.name,
                  oldPrice: toCurrency(basePrice),
                  newPrice: toCurrency(discountPrice),
                  discount: `${Math.round(((basePrice - discountPrice) / basePrice) * 100)}% OFF`,
                  image: toMediaUrl(product.image),
                });
              }
            });
          });
        });

        setDiscountFoods(deals.slice(0, 6));
      } catch {
        if (mounted) {
          setError("Failed to load homepage data from backend.");
        }
      } finally {
        if (mounted) {
          setLoading(false);
        }
      }
    }

    loadData();
    return () => {
      mounted = false;
    };
  }, []);

  const highlightedRestaurants = useMemo(() => restaurants.slice(0, 3), [restaurants]);
  const nearbyRestaurants = useMemo(() => {
    if (!location) {
      return restaurants.slice(0, 8);
    }
    const withDistance = restaurants
      .map((restaurant) => {
        const distance = haversineDistanceKm(
          { lat: location.lat, lng: location.lng },
          { lat: parseCoordinate(restaurant.latitude), lng: parseCoordinate(restaurant.longitude) },
        );
        return { restaurant, distance };
      })
      .filter((item) => item.distance !== null)
      .sort((a, b) => a.distance - b.distance);

    if (withDistance.length === 0) {
      return restaurants.slice(0, 8);
    }
    return withDistance.slice(0, 8).map((item) => item.restaurant);
  }, [restaurants, location]);
  const heroImage = toMediaUrl(restaurants[0]?.images?.[0]?.image_path) || "https://picsum.photos/id/1060/1280/720";

  return (
    <PageScaffold
      title="Discover local flavors"
      subtitle="Order from top restaurants with quick delivery and live order updates."
    >
      {error ? (
        <section className="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
          {error}
        </section>
      ) : null}

      <section className="space-y-3">
        <div className="flex items-center justify-between">
          <h2 className="text-lg font-semibold sm:text-xl">Categories</h2>
          <Link to="/categories" className="text-sm font-medium text-[#7A9E7E] hover:underline">
            View all categories
          </Link>
        </div>
        <div className="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-6">
          {(categories.length > 0 ? categories : [{ name: "Loading...", slug: "loading" }]).map((category) => (
            <CategoryCard
              key={category.slug || category.name}
              name={category.name}
              icon={categoryIcon(category.name)}
              href={`/restaurants?category=${encodeURIComponent(category.slug || category.name)}`}
            />
          ))}
        </div>
      </section>

      <section className="grid grid-cols-1 gap-5 rounded-2xl border border-[#E8B04A]/25 bg-[#F2E6D8] p-5 text-[#333333] shadow-[0_8px_20px_rgba(51,51,51,0.06)] sm:p-6 lg:grid-cols-2 lg:items-center">
        <div className="space-y-4">
          <p className="inline-flex rounded-full border border-[#E8B04A]/25 bg-[#FFF8F0] px-3 py-1 text-xs font-medium text-[#333333]/80">
            Multi-restaurant marketplace
          </p>
          <h2 className="text-2xl font-semibold tracking-tight sm:text-4xl">
            Find food you love, delivered fast
          </h2>
          <p className="text-sm text-[#333333]/75 sm:text-base">
            Search by restaurant name or cuisine, compare ETAs, and checkout in seconds.
          </p>

          <div className="flex flex-col gap-3 sm:flex-row">
            <input
              className="min-h-11 w-full rounded-xl border border-[#E8B04A]/35 bg-[#FFF8F0] px-4 py-2.5 text-sm text-[#333333] outline-none focus:ring-2 focus:ring-[#E8B04A]/40"
              placeholder="Search restaurants or cuisine"
            />
            <Link
              to="/restaurants"
              className="inline-flex min-h-11 items-center justify-center rounded-xl bg-[#E8B04A] px-5 py-2.5 text-sm font-semibold text-[#333333] transition hover:brightness-95"
            >
              Find food
            </Link>
          </div>
        </div>

        <div className="overflow-hidden rounded-xl border border-[#E8B04A]/25 bg-[#FFF8F0]">
          <img
            src={heroImage}
            srcSet={`${heroImage} 1280w`}
            sizes="(max-width: 1024px) 100vw, 50vw"
            width="1280"
            height="720"
            loading={loading ? "lazy" : "eager"}
            fetchPriority="high"
            alt="Assorted dishes from local restaurants"
            className="h-56 w-full object-cover sm:h-64 lg:h-[22rem]"
          />
        </div>
      </section>

      <section className="space-y-4">
        <h2 className="text-lg font-semibold sm:text-xl">Highlighted restaurants</h2>
        <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
          {highlightedRestaurants.map((restaurant) => (
            <RestaurantCard
              key={restaurant.slug}
              href={`/restaurants/${restaurant.slug}`}
              name={restaurant.name}
              image={toMediaUrl(restaurant.images?.[0]?.image_path) || "https://picsum.photos/id/292/720/420"}
              rating="4.6"
              deliveryTime={`${restaurant.city || "City"}`}
              category={`Min order ${toCurrency(restaurant.minimum_order_amount)}`}
              deliveryFee={toCurrency(restaurant.delivery_fee)}
              discountLabel="Fresh Picks"
              distanceKm={toRestaurantDistance(restaurant, location)}
            />
          ))}
        </div>
      </section>

      <section className="space-y-4">
        <div className="flex items-center justify-between">
          <h2 className="text-lg font-semibold sm:text-xl">Discount foods</h2>
          <Link to="/restaurants" className="text-sm text-[var(--color-accent)] hover:underline">
            View all offers
          </Link>
        </div>
        <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
          {discountFoods.map((deal) => (
            <ProductCard
              key={deal.id}
              name={deal.title}
              description={`${deal.restaurant} • was ${deal.oldPrice}`}
              price={deal.newPrice}
              image={deal.image || "https://picsum.photos/id/488/720/420"}
              badge={deal.discount}
              addLabel="Order"
              addHref="/restaurants"
            />
          ))}
        </div>
        {!loading && discountFoods.length === 0 ? (
          <p className="text-sm text-[var(--color-muted)]">
            No discounted foods available right now.
          </p>
        ) : null}
      </section>

      <section className="space-y-4">
        <h2 className="text-lg font-semibold sm:text-xl">Nearby restaurants</h2>
        <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
          {nearbyRestaurants.map((restaurant) => (
            <RestaurantCard
              key={restaurant.slug}
              href={`/restaurants/${restaurant.slug}`}
              name={restaurant.name}
              image={toMediaUrl(restaurant.images?.[0]?.image_path) || "https://picsum.photos/id/431/720/420"}
              rating="4.5"
              deliveryTime={restaurant.address_line || restaurant.city || "Nearby"}
              category={`${restaurant.city || "City"} • Min ${toCurrency(restaurant.minimum_order_amount)}`}
              deliveryFee={toCurrency(restaurant.delivery_fee)}
              distanceKm={toRestaurantDistance(restaurant, location)}
            />
          ))}
        </div>
      </section>

      <section className="space-y-4 rounded-2xl border border-[#E8B04A]/25 bg-[#F2E6D8] p-5 text-[#333333] shadow-[0_8px_20px_rgba(51,51,51,0.06)] sm:p-6">
        <div className="flex items-center justify-between">
          <h2 className="text-lg font-semibold sm:text-xl">Why people love ordering with us</h2>
          <span className="hidden rounded-full bg-[#FFF8F0] px-3 py-1 text-xs font-medium text-[#333333]/75 sm:inline-flex">
            Trusted daily by local food lovers
          </span>
        </div>
        <div className="grid grid-cols-1 gap-3 sm:grid-cols-3">
          <article className="group rounded-2xl border border-[#E8B04A]/30 bg-[#FFF8F0] p-4 transition hover:-translate-y-0.5 hover:shadow-[0_10px_22px_rgba(51,51,51,0.08)]">
            <div className="inline-flex h-10 w-10 items-center justify-center rounded-full bg-[#E8B04A]/20 text-lg">
              <Bike size={18} />
            </div>
            <h3 className="mt-3 font-semibold">Fast delivery</h3>
            <p className="mt-1 text-sm text-[#333333]/75">Average delivery time under 35 minutes.</p>
          </article>
          <article className="group rounded-2xl border border-[#E8B04A]/30 bg-[#FFF8F0] p-4 transition hover:-translate-y-0.5 hover:shadow-[0_10px_22px_rgba(51,51,51,0.08)]">
            <div className="inline-flex h-10 w-10 items-center justify-center rounded-full bg-[#7A9E7E]/20 text-lg">
              <Headset size={18} />
            </div>
            <h3 className="mt-3 font-semibold">Live support</h3>
            <p className="mt-1 text-sm text-[#333333]/75">Order help available throughout your checkout journey.</p>
          </article>
          <article className="group rounded-2xl border border-[#E8B04A]/30 bg-[#FFF8F0] p-4 transition hover:-translate-y-0.5 hover:shadow-[0_10px_22px_rgba(51,51,51,0.08)]">
            <div className="inline-flex h-10 w-10 items-center justify-center rounded-full bg-[#E8B04A]/20 text-lg">
              <ShieldCheck size={18} />
            </div>
            <h3 className="mt-3 font-semibold">Secure payments</h3>
            <p className="mt-1 text-sm text-[#333333]/75">Protected transactions with trusted payment partners.</p>
          </article>
        </div>
      </section>
    </PageScaffold>
  );
}
