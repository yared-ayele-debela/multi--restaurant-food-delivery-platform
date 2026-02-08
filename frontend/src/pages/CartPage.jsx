import { Link } from "react-router-dom";
import { ShoppingCart } from "lucide-react";
import PageScaffold from "../components/PageScaffold";
import { useCartBadge } from "../context/CartBadgeContext";

export default function CartPage() {
  const {
    cartLines,
    cartCount,
    subtotal,
    minimumOrder,
    updateLineQuantity,
    removeLine,
    clear,
  } = useCartBadge();

  const deliveryFee = cartCount > 0 ? 2.5 : 0;
  const tax = subtotal * 0.1;
  const total = subtotal + deliveryFee + tax;
  const remainingForMinimum = Math.max(0, minimumOrder - subtotal);
  const minimumProgress = Math.min(100, (subtotal / minimumOrder) * 100);
  const canCheckout = cartCount > 0 && subtotal >= minimumOrder;

  if (cartLines.length === 0) {
    return (
      <PageScaffold title="Your cart" subtitle="Items from one restaurant only.">
        <section className="space-y-4 rounded-2xl border border-[#E8B04A]/25 bg-[#F2E6D8] p-8 text-center text-[#333333] shadow-[0_8px_20px_rgba(51,51,51,0.06)]">
          <div className="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-[#FFF8F0] text-[#333333]">
            <ShoppingCart size={30} />
          </div>
          <h2 className="text-lg font-semibold">Your cart is empty</h2>
          <p className="text-sm text-[#333333]/75">
            Browse restaurants and add your first meal.
          </p>
          <div>
            <Link
              to="/restaurants"
              className="inline-flex min-h-11 items-center rounded-xl bg-[#E8B04A] px-5 py-2 text-sm font-semibold text-[#333333] shadow-sm transition hover:brightness-95 active:scale-95"
            >
              Explore restaurants
            </Link>
          </div>
        </section>
      </PageScaffold>
    );
  }

  return (
    <PageScaffold title="Your cart" subtitle="Items from one restaurant only.">
      <section className="grid grid-cols-1 gap-4 pb-24 lg:grid-cols-[1fr_340px] lg:pb-0">
        <div className="space-y-3">
          {cartLines.map((line) => (
            <article
              key={line.id}
              className="rounded-2xl border border-[#E8B04A]/25 bg-[#F2E6D8] p-4 text-[#333333] shadow-[0_8px_20px_rgba(51,51,51,0.06)]"
            >
              <div className="flex gap-3">
                <img
                  src={line.image}
                  width="120"
                  height="90"
                  loading="lazy"
                  alt={`${line.name} thumbnail`}
                  className="h-[90px] w-[120px] rounded-lg object-cover"
                />
                <div className="min-w-0 flex-1 space-y-1">
                  <h2 className="truncate font-semibold">{line.name}</h2>
                  <p className="text-xs text-[#333333]/75">Size: {line.sizeLabel}</p>
                  {line.addons.length > 0 ? (
                    <p className="text-xs text-[#333333]/75">
                      Add-ons:{" "}
                      {line.addons
                        .map((addon) => `${addon.label} x${addon.quantity}`)
                        .join(", ")}
                    </p>
                  ) : (
                    <p className="text-xs text-[#333333]/75">No add-ons</p>
                  )}
                  <p className="text-sm font-semibold text-[#7A9E7E]">${(line.unitTotal * line.quantity).toFixed(2)}</p>
                </div>
              </div>
              <div className="mt-3 flex flex-wrap items-center justify-between gap-2">
                <div className="flex items-center gap-2">
                  <button
                    type="button"
                    onClick={() => updateLineQuantity(line.id, line.quantity - 1)}
                    className="h-11 min-w-11 rounded-xl border border-[#E8B04A]/35 bg-[#FFF8F0] px-3 py-1 text-sm"
                  >
                    -
                  </button>
                  <span className="w-8 text-center text-sm">{line.quantity}</span>
                  <button
                    type="button"
                    onClick={() => updateLineQuantity(line.id, line.quantity + 1)}
                    className="h-11 min-w-11 rounded-xl border border-[#E8B04A]/35 bg-[#FFF8F0] px-3 py-1 text-sm"
                  >
                    +
                  </button>
                </div>
                <button
                  type="button"
                  onClick={() => removeLine(line.id)}
                  className="min-h-11 rounded-xl border border-[#D96C4A]/35 bg-[#FFF8F0] px-3 py-1 text-sm text-[#D96C4A]"
                >
                  Remove
                </button>
              </div>
            </article>
          ))}

          <button
            type="button"
            className="min-h-11 rounded-xl border border-[#E8B04A]/35 bg-[#FFF8F0] px-4 py-2 text-sm text-[#333333]"
            onClick={clear}
          >
            Clear cart
          </button>
        </div>

        <div className="h-fit space-y-4 rounded-2xl border border-[#E8B04A]/25 bg-[#F2E6D8] p-5 text-[#333333] shadow-[0_8px_20px_rgba(51,51,51,0.06)] lg:sticky lg:top-24">
          <h2 className="text-lg font-semibold">Order summary</h2>
          <div className="space-y-2 text-sm">
            <div className="flex items-center justify-between">
              <span className="text-[#333333]/75">Subtotal</span>
              <span>${subtotal.toFixed(2)}</span>
            </div>
            <div className="flex items-center justify-between">
              <span className="text-[#333333]/75">Delivery fee</span>
              <span>${deliveryFee.toFixed(2)}</span>
            </div>
            <div className="flex items-center justify-between">
              <span className="text-[#333333]/75">Taxes</span>
              <span>${tax.toFixed(2)}</span>
            </div>
            <div className="flex items-center justify-between border-t border-[#E8B04A]/25 pt-2 text-base font-semibold">
              <span>Total</span>
              <span className="text-[#7A9E7E]">${total.toFixed(2)}</span>
            </div>
          </div>

          <div className="space-y-2">
            <div className="flex items-center justify-between text-xs text-[#333333]/75">
              <span>Minimum order: ${minimumOrder.toFixed(2)}</span>
              <span>{Math.round(minimumProgress)}%</span>
            </div>
            <div className="h-2 overflow-hidden rounded-full bg-[#FFF8F0]">
              <div className="h-full bg-[#7A9E7E] transition-all" style={{ width: `${minimumProgress}%` }} />
            </div>
            {remainingForMinimum > 0 ? (
              <p className="text-xs text-[#D96C4A]">
                Add ${remainingForMinimum.toFixed(2)} more to reach minimum order.
              </p>
            ) : (
              <p className="text-xs text-[#7A9E7E]">Minimum order reached.</p>
            )}
          </div>

          {canCheckout ? (
            <Link
              to="/checkout"
              className="block min-h-11 rounded-xl bg-[#E8B04A] px-5 py-2.5 text-center text-sm font-semibold text-[#333333] transition hover:brightness-95"
            >
              Proceed to Checkout
            </Link>
          ) : (
            <button
              type="button"
              disabled
              className="block min-h-11 w-full rounded-xl bg-[#d8cdbc] px-5 py-2.5 text-sm font-medium text-[#333333]/60"
              title="Minimum order is not reached yet"
            >
              Proceed to Checkout
            </button>
          )}
        </div>
      </section>

      {cartCount > 0 ? (
        <div className="fixed inset-x-0 bottom-0 z-40 border-t border-[#E8B04A]/25 bg-[#FFF8F0]/95 p-3 backdrop-blur lg:hidden">
          <div className="mx-auto flex w-full max-w-7xl items-center gap-3">
            <div className="min-w-0 flex-1">
              <p className="text-xs text-[#333333]/75">{cartCount} items</p>
              <p className="text-sm font-semibold">${total.toFixed(2)}</p>
            </div>
            {canCheckout ? (
              <Link
                to="/checkout"
                className="inline-flex min-h-11 items-center justify-center rounded-xl bg-[#E8B04A] px-5 py-2 text-sm font-semibold text-[#333333]"
              >
                Checkout
              </Link>
            ) : (
              <button
                type="button"
                disabled
                className="inline-flex min-h-11 items-center justify-center rounded-xl bg-[#d8cdbc] px-5 py-2 text-sm font-medium text-[#333333]/60"
              >
                Add more
              </button>
            )}
          </div>
        </div>
      ) : null}
    </PageScaffold>
  );
}
