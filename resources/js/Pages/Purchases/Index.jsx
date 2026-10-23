import { Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import Pagination from '../../Components/Pagination';
import { inputClass, primaryButtonClass, secondaryButtonClass } from '../../Components/Field';
import { formatDate, formatNaira } from '../../lib/format';

export default function Index({ purchases, filters, totalCost }) {
    const [form, setForm] = useState({
        search: filters.search ?? '',
        from: filters.from ?? '',
        to: filters.to ?? '',
    });

    const submit = (event) => {
        event.preventDefault();
        router.get('/purchases', form, { preserveState: true, replace: true });
    };

    const clear = () => {
        setForm({ search: '', from: '', to: '' });
        router.get('/purchases');
    };

    const hasFilters = Object.values(filters).some((value) => value);

    return (
        <AppLayout>
            <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 className="text-2xl font-semibold">Purchases</h1>
                    <p className="text-sm text-stone-500">
                        {purchases.total} purchase(s) · Total cost in list: {formatNaira(totalCost)}
                    </p>
                </div>
                <Link href="/purchases/create" className={primaryButtonClass}>Record purchase</Link>
            </div>

            <form onSubmit={submit} className="mb-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                <input className={`${inputClass} lg:col-span-2`} placeholder="Search reference or seller"
                    value={form.search} onChange={(e) => setForm({ ...form, search: e.target.value })} />
                <input type="date" className={inputClass} value={form.from}
                    onChange={(e) => setForm({ ...form, from: e.target.value })} />
                <input type="date" className={inputClass} value={form.to}
                    onChange={(e) => setForm({ ...form, to: e.target.value })} />
                <div className="flex gap-2 sm:col-span-2 lg:col-span-4">
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
                            <th className="px-4 py-3 font-medium">Seller</th>
                            <th className="px-4 py-3 font-medium">Animals</th>
                            <th className="px-4 py-3 font-medium text-right">Total cost</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-stone-100">
                        {purchases.data.map((p) => (
                            <tr key={p.id} className="hover:bg-stone-50">
                                <td className="px-4 py-3 font-mono">
                                    <Link href={`/purchases/${p.id}`} className="text-green-700 hover:underline">{p.reference}</Link>
                                </td>
                                <td className="px-4 py-3">{formatDate(p.purchase_date)}</td>
                                <td className="px-4 py-3">{p.seller_name || '—'}</td>
                                <td className="px-4 py-3">{p.items_count}</td>
                                <td className="px-4 py-3 text-right">{formatNaira(p.total_cost)}</td>
                            </tr>
                        ))}
                        {purchases.data.length === 0 && (
                            <tr>
                                <td colSpan={5} className="px-4 py-10 text-center text-stone-500">
                                    {hasFilters ? 'No purchases match these filters.' : 'No purchases recorded yet.'}
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
            <Pagination links={purchases.links} />
        </AppLayout>
    );
}
