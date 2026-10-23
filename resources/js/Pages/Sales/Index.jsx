import { Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import Pagination from '../../Components/Pagination';
import { inputClass, primaryButtonClass, secondaryButtonClass } from '../../Components/Field';
import { PAYMENT_LABELS, formatDate, formatNaira } from '../../lib/format';

function Stat({ label, value }) {
    return (
        <div className="rounded-lg border border-stone-200 bg-white p-4">
            <div className="text-xs uppercase tracking-wide text-stone-500">{label}</div>
            <div className="mt-1 text-lg font-semibold">{formatNaira(value)}</div>
        </div>
    );
}

export default function Index({ sales, filters, paymentStatuses, totals }) {
    const [form, setForm] = useState({
        search: filters.search ?? '',
        payment_status: filters.payment_status ?? '',
        from: filters.from ?? '',
        to: filters.to ?? '',
    });

    const submit = (event) => {
        event.preventDefault();
        router.get('/sales', form, { preserveState: true, replace: true });
    };

    const clear = () => {
        setForm({ search: '', payment_status: '', from: '', to: '' });
        router.get('/sales');
    };

    const hasFilters = Object.values(filters).some((value) => value);

    return (
        <AppLayout>
            <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 className="text-2xl font-semibold">Sales</h1>
                    <p className="text-sm text-stone-500">Totals below cover the sales matching the current filters.</p>
                </div>
                <Link href="/sales/create" className={primaryButtonClass}>Record sale</Link>
            </div>

            <div className="mb-6 grid gap-4 sm:grid-cols-3">
                <Stat label="Sold (value)" value={totals.sold} />
                <Stat label="Received" value={totals.received} />
                <Stat label="Outstanding" value={totals.outstanding} />
            </div>

            <form onSubmit={submit} className="mb-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-5">
                <input className={`${inputClass} lg:col-span-2`} placeholder="Search reference or buyer"
                    value={form.search} onChange={(e) => setForm({ ...form, search: e.target.value })} />
                <select className={inputClass} value={form.payment_status}
                    onChange={(e) => setForm({ ...form, payment_status: e.target.value })}>
                    <option value="">All payment statuses</option>
                    {paymentStatuses.map((s) => <option key={s} value={s}>{PAYMENT_LABELS[s]}</option>)}
                </select>
                <input type="date" className={inputClass} value={form.from}
                    onChange={(e) => setForm({ ...form, from: e.target.value })} />
                <input type="date" className={inputClass} value={form.to}
                    onChange={(e) => setForm({ ...form, to: e.target.value })} />
                <div className="flex gap-2 sm:col-span-2 lg:col-span-5">
                    <button type="submit" className={primaryButtonClass}>Filter</button>
                    {hasFilters && <button type="button" onClick={clear} className={secondaryButtonClass}>Clear</button>}
                </div>
            </form>

            <div className="overflow-x-auto rounded-lg border border-stone-200 bg-white">
                <table className="min-w-full divide-y divide-stone-200 text-sm">
                    <thead className="bg-stone-50 text-left text-stone-600">
                        <tr>
                            <th className="px-4 py-3 font-medium">Reference</th>
                            <th className="px-4 py-3 font-medium">Date</th>
                            <th className="px-4 py-3 font-medium">Buyer</th>
                            <th className="px-4 py-3 font-medium">Animals</th>
                            <th className="px-4 py-3 font-medium text-right">Total</th>
                            <th className="px-4 py-3 font-medium text-right">Paid</th>
                            <th className="px-4 py-3 font-medium">Payment</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-stone-100">
                        {sales.data.map((s) => (
                            <tr key={s.id} className="hover:bg-stone-50">
                                <td className="px-4 py-3 font-mono">
                                    <Link href={`/sales/${s.id}`} className="text-green-700 hover:underline">{s.reference}</Link>
                                </td>
                                <td className="px-4 py-3">{formatDate(s.sale_date)}</td>
                                <td className="px-4 py-3">{s.buyer_name}</td>
                                <td className="px-4 py-3">{s.items_count}</td>
                                <td className="px-4 py-3 text-right">{formatNaira(s.total_amount)}</td>
                                <td className="px-4 py-3 text-right">{formatNaira(s.amount_paid)}</td>
                                <td className="px-4 py-3">{PAYMENT_LABELS[s.payment_status]}</td>
                            </tr>
                        ))}
                        {sales.data.length === 0 && (
                            <tr>
                                <td colSpan={7} className="px-4 py-10 text-center text-stone-500">
                                    {hasFilters ? 'No sales match these filters.' : 'No sales recorded yet.'}
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
            <Pagination links={sales.links} />
        </AppLayout>
    );
}
