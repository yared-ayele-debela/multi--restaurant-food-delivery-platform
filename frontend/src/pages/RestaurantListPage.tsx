import { useEffect, useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import { fetchRestaurants } from '../api/catalogApi'
import type { RestaurantSummary } from '../types/catalog'

export function RestaurantListPage() {
  const [page, setPage] = useState(1)
  const [restaurants, setRestaurants] = useState<RestaurantSummary[]>([])
  const [meta, setMeta] = useState<{ current_page: number; last_page: number; total: number } | null>(
    null,
  )
  const [q, setQ] = useState('')
  const [error, setError] = useState<string | null>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    let cancelled = false
    // eslint-disable-next-line react-hooks/set-state-in-effect -- fetch lifecycle
    setLoading(true)
    fetchRestaurants(page)
      .then(({ restaurants: rows, meta: m }) => {
        if (!cancelled) {
          setRestaurants(rows)
          setMeta(m)
          setError(null)
        }
      })
      .catch(() => {
        if (!cancelled) setError('Could not load restaurants.')
      })
      .finally(() => {
        if (!cancelled) setLoading(false)
      })
    return () => {
      cancelled = true
    }
  }, [page])

  const filtered = useMemo(() => {
    const s = q.trim().toLowerCase()
    if (!s) return restaurants
    return restaurants.filter(
      (r) =>
        r.name.toLowerCase().includes(s) || r.city.toLowerCase().includes(s),
    )
  }, [restaurants, q])

  return (
    <div className="fd-page">
      <h1>Restaurants</h1>
      <p className="fd-muted">Browse places that deliver in your area.</p>

      <div className="fd-toolbar">
        <input
          type="search"
          className="fd-input"
          placeholder="Filter by name or city"
          value={q}
          onChange={(e) => setQ(e.target.value)}
          aria-label="Filter restaurants"
        />
      </div>

      {loading ? <p className="fd-muted">Loading…</p> : null}
      {error ? <p className="fd-error">{error}</p> : null}

      {!loading && !error ? (
        <ul className="fd-card-list">
          {filtered.map((r) => (
            <li key={r.id} className="fd-card">
              <Link to={`/restaurants/${r.slug}`} className="fd-card-link">
                <span className="fd-card-title">{r.name}</span>
                <span className="fd-card-meta">
                  {r.city} · Min. {r.minimum_order_amount} · Delivery {r.delivery_fee}
                </span>
              </Link>
            </li>
          ))}
        </ul>
      ) : null}

      {!loading && meta && meta.last_page > 1 ? (
        <div className="fd-pagination">
          <button
            type="button"
            className="fd-secondary"
            disabled={page <= 1}
            onClick={() => setPage((p) => Math.max(1, p - 1))}
          >
            Previous
          </button>
          <span className="fd-muted">
            Page {meta.current_page} of {meta.last_page} ({meta.total} total)
          </span>
          <button
            type="button"
            className="fd-secondary"
            disabled={page >= meta.last_page}
            onClick={() => setPage((p) => p + 1)}
          >
            Next
          </button>
        </div>
      ) : null}
    </div>
  )
}
