import { Link } from "react-router-dom";
import PageScaffold from "../components/PageScaffold";

const orders = [
  {
    id: 101,
    restaurant: "Urban Grill",
    date: "Mar 29, 2026",
    total: 28.4,
    status: "on_the_way",
  },
  {
    id: 102,
    restaurant: "Pizza Corner",
    date: "Mar 26, 2026",
    total: 18.4,
    status: "delivered",
  },
  {
    id: 103,
    restaurant: "Spice Kitchen",
    date: "Mar 22, 2026",
    total: 33.1,
    status: "cancelled",
  },
];

const statusMap = {
  placed: { label: "Pending", className: "bg-[#E8B04A]/20 text-[#333333]" },
  confirmed: { label: "Pending", className: "bg-[#E8B04A]/20 text-[#333333]" },
  on_the_way: {
    label: "Pending",
    className: "bg-[#E8B04A]/20 text-[#333333]",
  },
  delivered: {
    label: "Delivered",
    className: "bg-[#7A9E7E]/20 text-[#7A9E7E]",
  },
  cancelled: {
    label: "Cancelled",
    className: "bg-[#D96C4A]/20 text-[#D96C4A]",
  },
};

export default function OrdersPage() {
  return (
    <PageScaffold title="Your orders" subtitle="Track status and open order details.">
      <section className="space-y-3">
        {orders.map((order) => (
          <article
            key={order.id}
            className="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-[#E8B04A]/25 bg-[#F2E6D8] p-5 text-[#333333] shadow-[0_8px_20px_rgba(51,51,51,0.06)]"
          >
            <div>
              <h2 className="font-semibold">Order #{order.id}</h2>
              <p className="text-sm text-[#333333]/75">
                {order.restaurant} • {order.date}
              </p>
              <p className="text-sm font-medium text-[#7A9E7E]">${order.total.toFixed(2)}</p>
            </div>
            <span className={`rounded-full px-2.5 py-1 text-xs font-medium ${statusMap[order.status].className}`}>
              {statusMap[order.status].label}
            </span>
            <Link
              to={`/orders/${order.id}`}
              className="min-h-11 rounded-xl border border-[#E8B04A]/35 bg-[#FFF8F0] px-3 py-1.5 text-sm text-[#333333]"
            >
              View details
            </Link>
          </article>
        ))}
      </section>

      <section className="rounded-2xl border border-[#E8B04A]/25 bg-[#F2E6D8] p-5 text-[#333333] shadow-[0_8px_20px_rgba(51,51,51,0.06)]">
        <h3 className="font-semibold">Need help with an order?</h3>
        <p className="mt-1 text-sm text-[#333333]/75">
          Contact support and include your order number for faster help.
        </p>
      </section>
    </PageScaffold>
  );
}
