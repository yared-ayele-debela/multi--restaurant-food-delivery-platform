import { Link, useParams } from "react-router-dom";
import { useEffect, useMemo, useState } from "react";
import PageScaffold from "../components/PageScaffold";
import { useCartBadge } from "../context/CartBadgeContext";
import { Toast } from "../components/ui/primitives";
import { fetchRestaurant, toMediaUrl } from "../api/catalogApi";

function normalizeTagList(value) {
  if (Array.isArray(value)) {
    return value
      .map((item) => {
        if (typeof item === "string") {
          return item.trim();
        }
        if (item && typeof item === "object") {
          return String(item.name || item.label || item.value || "").trim();
        }
        return String(item ?? "").trim();
      })
      .filter(Boolean);
  }

  if (!value) {
    return [];
  }

  if (typeof value === "string") {
    return value
      .split(",")
      .map((item) => item.trim())
      .filter(Boolean);
  }

  if (typeof value === "object") {
    return Object.values(value)
      .map((item) => String(item ?? "").trim())
      .filter(Boolean);
  }

  return [String(value).trim()].filter(Boolean);
}

export default function ProductPage() {
  const { slug, productId } = useParams();
  const { addLine, replaceCartWithLine, cartRestaurantSlug } = useCartBadge();
  const [heroImage, setHeroImage] = useState("");
  const [size, setSize] = useState("");
  const [quantity, setQuantity] = useState(1);
  const [selectedAddons, setSelectedAddons] = useState({});
  const [errorMessage, setErrorMessage] = useState("");
  const [loading, setLoading] = useState(true);
  const [loadError, setLoadError] = useState("");
  const [restaurant, setRestaurant] = useState(null);
  const [product, setProduct] = useState(null);
  const [toast, setToast] = useState("");
  const [animateCart, setAnimateCart] = useState(false);

  useEffect(() => {
    let mounted = true;
    async function loadProduct() {
      try {
        setLoading(true);
        setLoadError("");
        const data = await fetchRestaurant(slug);
        if (!mounted) {
          return;
        }
        setRestaurant(data);
        const flatProducts = (data.categories || []).flatMap((category) => category.products || []);
        const foundProduct = flatProducts.find((item) => String(item.id) === String(productId));
        if (!foundProduct) {
          setLoadError("Product not found in restaurant menu.");
          setProduct(null);
          return;
        }
        setProduct(foundProduct);
        setHeroImage(toMediaUrl(foundProduct.image) || toMediaUrl(data.images?.[0]?.image_path));
        const defaultSize = (foundProduct.sizes || []).find((item) => item.is_default) || foundProduct.sizes?.[0];
        setSize(defaultSize ? String(defaultSize.id) : "");
      } catch {
        if (mounted) {
          setLoadError("Failed to load product from backend.");
        }
      } finally {
        if (mounted) {
          setLoading(false);
        }
      }
    }
    if (slug && productId) {
      loadProduct();
    }
    return () => {
      mounted = false;
    };
  }, [slug, productId]);

  const sizeOptions = useMemo(() => {
    const options = (product?.sizes || []).map((item) => ({
      value: String(item.id),
      label: `${item.name} (${Number.parseFloat(item.price || "0").toFixed(2)})`,
      price: Number.parseFloat(item.price || "0"),
    }));
    return [{ value: "", label: "Select size", price: 0 }, ...options];
  }, [product]);

  const addonCatalog = useMemo(
    () =>
      (product?.addons || []).map((item) => ({
        id: String(item.id),
        label: item.name,
        price: Number.parseFloat(item.price || "0"),
      })),
    [product],
  );

  const productName = product?.name || `Product #${productId}`;
  const basePrice = useMemo(() => Number.parseFloat(product?.base_price || "0"), [product]);
  const productTags = useMemo(() => {
    const dietaryTags = normalizeTagList(product?.dietary_info);
    const allergenTags = normalizeTagList(product?.allergens);
    return [...dietaryTags, ...allergenTags].slice(0, 6);
  }, [product]);

  const totalPrice = useMemo(() => {
    const selectedSize = sizeOptions.find((option) => option.value === size);
    const sizePrice = selectedSize?.price ?? basePrice;
    const addonTotal = Object.entries(selectedAddons).reduce((sum, [key, addonQuantity]) => {
      const addon = addonCatalog.find((item) => item.id === key);
      if (!addon || addonQuantity <= 0) {
        return sum;
      }
      return sum + addon.price * addonQuantity;
    }, 0);
    return (sizePrice + addonTotal) * quantity;
  }, [selectedAddons, quantity, size, sizeOptions, addonCatalog, basePrice]);

  const toggleAddon = (addonId) => {
    setSelectedAddons((prev) => {
      if (prev[addonId]) {
        const copy = { ...prev };
        delete copy[addonId];
        return copy;
      }
      return { ...prev, [addonId]: 1 };
    });
  };

  const updateAddonQuantity = (addonId, nextQty) => {
    setSelectedAddons((prev) => {
      if (nextQty <= 0) {
        const copy = { ...prev };
        delete copy[addonId];
        return copy;
      }
      return { ...prev, [addonId]: nextQty };
    });
  };

  const handleAddToCart = () => {
    if (!size) {
      setErrorMessage("Please choose a size before adding to cart.");
      return;
    }

    setErrorMessage("");

    const selectedSize = sizeOptions.find((option) => option.value === size) || sizeOptions[1];
    const selectedAddonRows = Object.entries(selectedAddons)
      .map(([addonId, addonQuantity]) => {
        const addon = addonCatalog.find((item) => item.id === addonId);
        if (!addon || addonQuantity <= 0) {
          return null;
        }
        return {
          id: addon.id,
          label: addon.label,
          quantity: addonQuantity,
          price: addon.price,
        };
      })
      .filter(Boolean);

    const lineSignature = JSON.stringify({
      productId,
      size,
      addons: selectedAddonRows.map((row) => ({ id: row.id, quantity: row.quantity })),
    });

    const line = {
      id: `${Date.now()}-${Math.random().toString(16).slice(2, 7)}`,
      productId: String(productId),
      name: productName,
      image: heroImage,
      sizeLabel: selectedSize?.label ?? "Regular",
      addons: selectedAddonRows,
      quantity,
      unitTotal: totalPrice / quantity,
      signature: lineSignature,
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

    setAnimateCart(true);
    setToast("Added to cart.");
    window.setTimeout(() => setAnimateCart(false), 300);
  };

  return (
    <PageScaffold title="Product details" subtitle="Choose size and extras before adding to cart.">
      {loading ? (
        <section className="rounded-2xl border border-[#E8B04A]/25 bg-[#F2E6D8] p-4 text-sm text-[#333333]/75 shadow-[0_8px_20px_rgba(51,51,51,0.06)]">
          Loading product...
        </section>
      ) : null}
      {loadError ? (
        <section className="rounded-2xl border border-[#D96C4A]/35 bg-[#FFF8F0] p-4 text-sm text-[#D96C4A] shadow-[0_8px_20px_rgba(51,51,51,0.06)]">
          {loadError}
        </section>
      ) : null}
      <section className="grid grid-cols-1 gap-4 rounded-2xl border border-[#E8B04A]/25 bg-[#F2E6D8] p-5 text-[#333333] shadow-[0_8px_20px_rgba(51,51,51,0.06)] lg:grid-cols-2">
        <div className="space-y-3">
          <img
            src={heroImage}
            srcSet={`${heroImage.replace("/960/640", "/640/426")} 640w, ${heroImage} 960w`}
            sizes="(max-width: 1024px) 100vw, 50vw"
            width="960"
            height="640"
            loading="eager"
            alt={`${productName} hero`}
            className="h-60 w-full rounded-xl object-cover"
          />
          <div className="grid grid-cols-3 gap-2">
            {[
              toMediaUrl(product?.image),
              toMediaUrl(restaurant?.images?.[0]?.image_path),
              toMediaUrl(restaurant?.images?.[1]?.image_path),
            ]
              .filter(Boolean)
              .map((image) => (
              <button
                key={image}
                type="button"
                onClick={() => setHeroImage(image)}
                className={`overflow-hidden rounded-lg border ${
                  heroImage === image ? "border-[#E8B04A]" : "border-[#E8B04A]/35"
                } min-h-11`}
              >
                <img src={image} width="160" height="120" loading="lazy" alt="Food preview" className="h-16 w-full object-cover" />
              </button>
              ))}
          </div>
        </div>

        <div className="space-y-4">
          <div>
            <h2 className="text-xl font-semibold">{productName}</h2>
            <p className="mt-1 text-sm text-[#333333]/75">
              {product?.description || "Fresh ingredients, prepared by the restaurant."}
            </p>
            <div className="mt-2 flex flex-wrap gap-2">
              {productTags.map((tag) => (
                <span
                  key={tag}
                  className="rounded-full border border-[#E8B04A]/35 bg-[#FFF8F0] px-2.5 py-1 text-xs text-[#333333]/75"
                >
                  {tag}
                </span>
              ))}
            </div>
          </div>

          <label className="block text-sm font-medium" htmlFor="size-selector">
            Size (required)
          </label>
          <select
            id="size-selector"
            value={size}
            onChange={(event) => setSize(event.target.value)}
            className="min-h-11 w-full rounded-xl border border-[#E8B04A]/35 bg-[#FFF8F0] px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-[#E8B04A]/40"
          >
            {sizeOptions.map((option) => (
              <option key={option.value || "placeholder"} value={option.value}>
                {option.label}
              </option>
            ))}
          </select>

          <fieldset className="space-y-2">
            <legend className="text-sm font-medium">Add-ons</legend>
            {addonCatalog.map((addon) => {
              const addonQty = selectedAddons[addon.id] ?? 0;
              const selected = addonQty > 0;
              return (
                <div
                  key={addon.id}
                  className="rounded-xl border border-[#E8B04A]/30 bg-[#FFF8F0] px-3 py-2"
                >
                  <div className="flex items-center justify-between gap-3">
                    <label className="flex cursor-pointer items-center gap-2 text-sm">
                      <input
                        type="checkbox"
                        checked={selected}
                        onChange={() => toggleAddon(addon.id)}
                        className="h-4 w-4 rounded border-[#E8B04A]/60 text-[#E8B04A] focus:ring-[#E8B04A]/40"
                      />
                      {addon.label} (+${addon.price.toFixed(2)})
                    </label>
                    {selected && (
                      <div className="flex items-center gap-2">
                        <button
                          type="button"
                          className="h-11 min-w-11 rounded-xl border border-[#E8B04A]/35 bg-[#FFF8F0] px-2"
                          onClick={() => updateAddonQuantity(addon.id, addonQty - 1)}
                        >
                          -
                        </button>
                        <span className="w-4 text-center text-sm">{addonQty}</span>
                        <button
                          type="button"
                          className="h-11 min-w-11 rounded-xl border border-[#E8B04A]/35 bg-[#FFF8F0] px-2"
                          onClick={() => updateAddonQuantity(addon.id, addonQty + 1)}
                        >
                          +
                        </button>
                      </div>
                    )}
                  </div>
                </div>
              );
            })}
          </fieldset>

          <div className="space-y-2">
            <p className="text-sm font-medium">Quantity</p>
            <div className="flex items-center gap-2">
              <button
                type="button"
                onClick={() => setQuantity((prev) => Math.max(1, prev - 1))}
                className="h-11 min-w-11 rounded-xl border border-[#E8B04A]/35 bg-[#FFF8F0] px-3 py-1.5"
              >
                -
              </button>
              <span className="min-w-8 text-center">{quantity}</span>
              <button
                type="button"
                onClick={() => setQuantity((prev) => prev + 1)}
                className="h-11 min-w-11 rounded-xl border border-[#E8B04A]/35 bg-[#FFF8F0] px-3 py-1.5"
              >
                +
              </button>
            </div>
          </div>

          {errorMessage ? (
            <p className="rounded-xl border border-[#D96C4A]/35 bg-[#FFF8F0] px-3 py-2 text-sm text-[#D96C4A]">
              {errorMessage}
            </p>
          ) : null}

          <button
            type="button"
            onClick={handleAddToCart}
            className={`min-h-11 w-full rounded-xl bg-[#E8B04A] px-4 py-2.5 text-sm font-semibold text-[#333333] transition hover:brightness-95 ${
              animateCart ? "scale-[1.02]" : ""
            }`}
          >
            Add to cart • ${totalPrice.toFixed(2)}
          </button>

          <Link to={`/restaurants/${slug}`} className="inline-block text-sm font-medium text-[#7A9E7E] hover:underline">
            Back to restaurant
          </Link>
        </div>
      </section>
      <Toast open={Boolean(toast)} message={toast} onDone={() => setToast("")} />
    </PageScaffold>
  );
}
