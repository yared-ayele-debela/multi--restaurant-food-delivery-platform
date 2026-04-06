import { Link } from "react-router-dom";

export default function RestaurantCard({
  href,
  name,
  image,
  rating,
  deliveryTime,
  category,
  deliveryFee,
  discountLabel,
  distanceKm,
  isFavorite = false,
  onToggleFavorite,
}) {
  const content = (
    <article className="overflow-hidden rounded-2xl bg-[#F2E6D8] p-4 text-[#333333] shadow-[0_6px_18px_rgba(51,51,51,0.08)] transition duration-300 hover:scale-[1.02] hover:shadow-[0_12px_24px_rgba(51,51,51,0.14)]">
      <div className="relative overflow-hidden rounded-xl">
        <img
          src={image}
          alt={`${name} food preview`}
          className="h-44 w-full rounded-xl object-cover"
          loading="lazy"
        />
        {discountLabel ? (
          <span className="absolute left-3 top-3 rounded-full bg-[#D96C4A] px-3 py-1 text-xs font-semibold text-white">
            {discountLabel}
          </span>
        ) : null}
        {typeof distanceKm === "number" && Number.isFinite(distanceKm) ? (
          <span className="absolute bottom-3 left-3 rounded-full bg-[#333333]/80 px-2.5 py-1 text-[11px] font-semibold text-[#FFF8F0]">
            {distanceKm.toFixed(1)} km away
          </span>
        ) : null}
        <button
          type="button"
          aria-label={`Favorite ${name}`}
          onClick={(event) => {
            event.preventDefault();
            onToggleFavorite?.();
          }}
          className="absolute right-3 top-3 inline-flex min-h-11 min-w-11 items-center justify-center rounded-full bg-[#FFF8F0]/90 text-lg text-[#D96C4A] shadow-sm transition active:scale-95"
        >
          {isFavorite ? "♥" : "♡"}
        </button>
      </div>

      <div className="mt-4 space-y-1.5">
        <h3 className="text-base font-semibold">{name}</h3>
        <p className="text-sm text-[#7A9E7E]">
          ⭐ {rating} • {deliveryTime}
        </p>
        <p className="text-sm text-[#333333]/80">{category}</p>
        {deliveryFee ? <p className="text-xs text-[#333333]/70">Delivery {deliveryFee}</p> : null}
      </div>
    </article>
  );

  if (href) {
    return <Link to={href}>{content}</Link>;
  }

  return content;
}
