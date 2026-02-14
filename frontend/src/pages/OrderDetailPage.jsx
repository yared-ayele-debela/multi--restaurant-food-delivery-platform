import { useParams } from "react-router-dom";
import PageScaffold from "../components/PageScaffold";

const timeline = [
  { id: "placed", label: "Placed", time: "12:10 PM", done: true },
  { id: "confirmed", label: "Confirmed", time: "12:14 PM", done: true },
  { id: "on_way", label: "On the way", time: "12:28 PM", done: true },
  { id: "delivered", label: "Delivered", time: "12:48 PM", done: false },
];

const orderItems = [
  { id: "l1", name: "Classic Burger", modifiers: "Large, Extra cheese", qty: 2, price: 10.7 },
  { id: "l2", name: "Fries Box", modifiers: "No add-ons", qty: 1, price: 3.4 },
];

export default function OrderDetailPage() {
  const { id } = useParams();

  return (
    <PageScaffold title={`Order #${id}`} subtitle="Order timeline and item breakdown.">
      <section className="grid grid-cols-1 gap-4 lg:grid-cols-[1.2fr_1fr]">
        <article className="rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-4">
          <h2 className="font-semibold">Items</h2>
          <div className="mt-3 space-y-3">
            {orderItems.map((item) => (
              <div key={item.id} className="flex items-start justify-between gap-3 text-sm">
                <div>
                  <p className="font-medium">
                    {item.name} x{item.qty}
                  </p>
                  <p className="text-[var(--color-muted)]">{item.modifiers}</p>
                </div>
                <p>${(item.qty * item.price).toFixed(2)}</p>
              </div>
            ))}
          </div>
          <div className="mt-4 border-t border-[var(--color-border)] pt-3 text-sm">
            <div className="flex items-center justify-between">
              <span className="text-[var(--color-muted)]">Subtotal</span>
              <span>$24.80</span>
            </div>
            <div className="flex items-center justify-between">
              <span className="text-[var(--color-muted)]">Delivery</span>
              <span>$2.50</span>
            </div>
            <div className="mt-2 flex items-center justify-between text-base font-semibold">
              <span>Total</span>
              <span>$27.30</span>
            </div>
          </div>
        </article>

        <article className="rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-4">
          <h2 className="font-semibold">Timeline</h2>
          <ol className="mt-3 space-y-3">
            {timeline.map((step) => (
              <li key={step.id} className="flex items-start gap-3 text-sm">
                <span
                  className={`mt-0.5 inline-block h-2.5 w-2.5 rounded-full ${
                    step.done ? "bg-emerald-500" : "bg-slate-300 dark:bg-slate-700"
                  }`}
                />
                <div>
                  <p className={step.done ? "font-medium" : "text-[var(--color-muted)]"}>{step.label}</p>
                  <p className="text-xs text-[var(--color-muted)]">{step.time}</p>
                </div>
              </li>
            ))}
          </ol>
        </article>
      </section>
    </PageScaffold>
  );
}
