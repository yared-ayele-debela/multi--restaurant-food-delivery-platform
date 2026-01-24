export default function PageScaffold({ title, subtitle, children }) {
  return (
    <section className="space-y-6">
      <header className="space-y-2">
        <h1 className="text-2xl font-semibold tracking-tight sm:text-3xl">{title}</h1>
        {subtitle ? <p className="text-[var(--color-muted)]">{subtitle}</p> : null}
      </header>
      {children}
    </section>
  );
}
