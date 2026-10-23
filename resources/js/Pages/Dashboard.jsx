import { Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../Layouts/AppLayout';
import StatusBadge from '../Components/StatusBadge';
import { inputClass, primaryButtonClass, secondaryButtonClass } from '../Components/Field';
import { HEALTH_LABELS, PAYMENT_LABELS, formatDate, formatNaira } from '../lib/format';

function Card({ label, value, sub }) {
    return (
        <div className="rounded-lg border border-stone-200 bg-white p-5">
            <div className="text-xs uppercase tracking-wide text-stone-500">{label}</div>
            <div className="mt-1 text-2xl font-semibold">{value}</div>
            {sub && <div className="mt-1 text-xs text-stone-500">{sub}</div>}
        </div>
    );
}

function Empty({ text }) {
    return <p className="py-6 text-center text-sm text-stone-500">{text}</p>;
}

export default function Dashboard({ animals, types, attention, recentFeeding, recentSales, period, money }) {
    const [range, setRange] = useState({ from: period.from, to: period.to });
    const maxTotal = Math.max(1, ...types.map((t) => t.total));

    const applyRange = (event) => {
        event.preventDefault();
        router.get('/', range, { preserveState: true, replace: true });
    };

    return (
        <AppLayout>
            <div className="mb-6 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-semibold">Dashboard</h1>
                    <p className="text-sm text-stone-500">All figures come from the farm's recorded data.</p>
                </div>
                <form onSubmit={applyRange} className="flex flex-wrap items-end gap-2">
                    <div>
                        <label className="mb-1 block text-xs text-stone-500">From</label>
                        <input type="date" className={inputClass} value={range.from}
                            onChange={(e) => setRange({ ...range, from: e.target.value })} />
                    </div>
                    <div>
                        <label className="mb-1 block text-xs text-stone-500">To</label>
                        <input type="date" className={inputClass} value={range.to}
                            onChange={(e) => setRange({ ...range, to: e.target.value })} />
                    </div>
                    <button type="submit" className={primaryButtonClass}>Apply</button>
                </form>
            </div>

            <section className="mb-8">
                <h2 className="mb-3 font-semibold">Animals</h2>
                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <Card label="Total registered" value={animals.total} />
                    <Card label="Active" value={animals.active} />
                    <Card label="Sold" value={animals.sold} />
                    <Card label="Deceased / archived" value={animals.deceased + animals.archived} />
                </div>
            </section>

            <section className="mb-8 rounded-lg border border-stone-200 bg-white p-5">
                <h2 className="mb-4 font-semibold">Active animals by type</h2>
                {types.length === 0 ? (
                    <Empty text="No animal types yet. Create one under Animal Types." />
                ) : (
                    <div className="space-y-3">
                        {types.map((t) => (
                            <div key={t.id}>
                                <div className="mb-1 flex justify-between text-sm">
                                    <span>{t.name}</span>
                                    <span className="text-stone-500">{t.active} active · {t.total} total</span>
                                </div>
                                <div className="h-3 w-full rounded bg-stone-100">
                                    <div className="h-3 rounded bg-green-600"
                                        style={{ width: `${(t.total / maxTotal) * 100}%` }} />
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </section>

            <section className="mb-8">
                <h2 className="mb-1 font-semibold">Money for {formatDate(period.from)} to {formatDate(period.to)}</h2>
                <p className="mb-3 text-xs text-stone-500">
                    These figures are shown separately. They are not a profit figure, because acquisition and other costs are not netted off here.
                </p>
                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                    <Card label="Sales value" value={formatNaira(money.sales_value)} />
                    <Card label="Received" value={formatNaira(money.received)} />
                    <Card label="Outstanding on these sales" value={formatNaira(money.outstanding)} />
                    <Card label="Purchases" value={formatNaira(money.purchases)} />
                    <Card label="Expenses" value={formatNaira(money.expenses)} />
                </div>
            </section>

            <div className="grid gap-6 xl:grid-cols-3">
                <section className="rounded-lg border border-stone-200 bg-white p-5">
                    <div className="mb-3 flex items-center justify-between">
                        <h2 className="font-semibold">Health needing attention</h2>
                        <Link href="/health-records" className="text-sm text-green-700 hover:underline">All</Link>
                    </div>
                    <p className="mb-3 text-xs text-stone-500">Sick or monitoring observations from the last 30 days.</p>
                    {attention.length === 0 ? <Empty text="No animals flagged in the last 30 days." /> : (
                        <ul className="divide-y divide-stone-100">
                            {attention.map((r) => (
                                <li key={r.id} className="py-2 text-sm">
                                    <Link href={`/animals/${r.animal_id}`} className="font-mono text-green-700 hover:underline">{r.animal?.animal_code}</Link>
                                    <span className="ml-2 text-stone-500">{HEALTH_LABELS[r.health_status]} · {formatDate(r.observation_date)}</span>
                                    <div className="text-stone-700">{r.observed_condition}</div>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>

                <section className="rounded-lg border border-stone-200 bg-white p-5">
                    <div className="mb-3 flex items-center justify-between">
                        <h2 className="font-semibold">Recent feeding</h2>
                        <Link href="/feeding-records" className="text-sm text-green-700 hover:underline">All</Link>
                    </div>
                    {recentFeeding.length === 0 ? <Empty text="No feeding recorded yet." /> : (
                        <ul className="divide-y divide-stone-100">
                            {recentFeeding.map((r) => (
                                <li key={r.id} className="py-2 text-sm">
                                    <Link href={`/animals/${r.animal_id}`} className="font-mono text-green-700 hover:underline">{r.animal?.animal_code}</Link>
                                    <span className="ml-2">{r.feed_type}</span>
                                    <div className="text-stone-500">{r.quantity} {r.unit} · {formatDate(r.fed_at)}</div>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>

                <section className="rounded-lg border border-stone-200 bg-white p-5">
                    <div className="mb-3 flex items-center justify-between">
                        <h2 className="font-semibold">Recent sales</h2>
                        <Link href="/sales" className="text-sm text-green-700 hover:underline">All</Link>
                    </div>
                    {recentSales.length === 0 ? <Empty text="No sales recorded yet." /> : (
                        <ul className="divide-y divide-stone-100">
                            {recentSales.map((s) => (
                                <li key={s.id} className="py-2 text-sm">
                                    <Link href={`/sales/${s.id}`} className="font-mono text-green-700 hover:underline">{s.reference}</Link>
                                    <span className="ml-2">{s.buyer_name}</span>
                                    <div className="text-stone-500">
                                        {formatNaira(s.total_amount)} · {PAYMENT_LABELS[s.payment_status]} · {formatDate(s.sale_date)}
                                    </div>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
            </div>

            <div className="mt-8 flex flex-wrap gap-2">
                <Link href="/reports" className={secondaryButtonClass}>Open reports</Link>
                <Link href="/animals/create" className={secondaryButtonClass}>Add animal</Link>
            </div>
        </AppLayout>
    );
}
