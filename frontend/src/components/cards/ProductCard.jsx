import { Link } from "react-router-dom";

export default function ProductCard({
  name,
  description,
  price,
  image,
  addLabel = "Add",
  onAdd,
  addHref,
  disabled = false,
  horizontal = false,
  stickyAddMobile = false,
  badge,
}) {
  const actionClassName =
    "inline-flex min-h-11 items-center justify-center rounded-xl bg-[#E8B04A] px-4 py-2 text-sm font-semibold text-[#333333] transition duration-200 active:scale-95 disabled:cursor-not-allowed disabled:bg-[#d8cdbc] disabled:text-[#333333]/60";

  const action = onAdd || !addHref || disabled ? (
    <button
      type="button"
      onClick={onAdd}
      disabled={disabled}
      className={actionClassName}
    >
      {addLabel}
    </button>
  ) : (
    <Link to={addHref} className={actionClassName}>
      {addLabel}
    </Link>
  );

  return (
    <article className="rounded-2xl bg-[#F2E6D8] p-4 text-[#333333] shadow-[0_6px_18px_rgba(51,51,51,0.08)] transition duration-300 hover:scale-[1.02] hover:shadow-[0_12px_24px_rgba(51,51,51,0.14)]">
      <div className={horizontal ? "flex gap-3" : "space-y-3"}>
        <img
          src={image}
          alt={`${name} preview`}
          className={horizontal ? "h-28 w-28 rounded-xl object-cover" : "h-36 w-full rounded-xl object-cover"}
          loading="lazy"
        />
        <div className="min-w-0 flex-1">
          {badge ? (
            <span className="inline-flex rounded-full bg-[#D96C4A] px-2.5 py-1 text-xs font-semibold text-white">
              {badge}
            </span>
          ) : null}
          <h3 className="mt-2 truncate text-base font-semibold">{name}</h3>
          <p className="truncate text-sm text-[#333333]/75">{description}</p>
          <div className="mt-3 flex items-center justify-between gap-2">
            <p className="text-base font-bold text-[#E8B04A]">{price}</p>
            <div className="hidden sm:block">{action}</div>
          </div>
        </div>
      </div>

      <div className={`mt-3 sm:hidden ${stickyAddMobile ? "sticky bottom-3 z-10" : ""}`}>{action}</div>
    </article>
  );
}
