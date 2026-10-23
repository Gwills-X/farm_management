import { Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import Pagination from '../../Components/Pagination';
import { inputClass, primaryButtonClass, secondaryButtonClass } from '../../Components/Field';
import { formatDate, formatNaira } from '../../lib/format';

export default function Index({ expenses, filters, categories, total, canDelete }) {
    const [form, setForm] = useState({
        search: filters.search ?? '',
        expense_category_id: filters.expense_category_id ?? '',
        from: filters.from ?? '',
        to: filters.to ?? '',
    });

    const submit = (event) => {
        event.preventDefault();
        router.get('/expenses', form, { preserveState: true, replace: true });
    };

    const clear = () => {
        setForm({ search: '', expense_category_id: '', from: '', to: '' });
        router.get('/expenses');
    };

    const destroy = (expense) => {
        if (window.confirm('Delete this expense? This cannot be undone.')) {
            router.delete(`/expenses/${expense.id}`);
        }
    };

    const hasFilters = Object.values(filters).some((value) => value);

    return (
        <AppLayout>
            <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 className="text-2xl font-semibold">Expenses</h1>
                    <p className="text-sm text-stone-500">Total for the filtered list: {formatNaira(total)}</p>
                </div>
                <Link href="/expenses/create" className={primaryButtonClass}>Record expense</Link>
            </div>

            <form onSubmit={submit} className="mb-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-5">
                <input className={`${inputClass} lg:col-span-2`} placeholder="Search description"
                    value={form.search} onChange={(e) => setForm({ ...form, search: e.target.value })} />
                <select className={inputClass} value={form.expense_category_id}
                    onChange={(e) => setForm({ ...form, expense_category_id: e.target.value })}>
                    <option value="">All categories</option>
                    {categories.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
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
                            <th className="px-4 py-3 font-medium">Date</th>
                            <th className="px-4 py-3 font-medium">Category</th>
                            <th className="px-4 py-3 font-medium">Description</th>
                            <th className="px-4 py-3 font-medium text-right">Amount</th>
                            <th className="px-4 py-3 font-medium">Recorded by</th>
                            <th className="px-4 py-3 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-stone-100">
                        {expenses.data.map((e) => (
                            <tr key={e.id}>
                                <td className="px-4 py-3">{formatDate(e.expense_date)}</td>
                                <td className="px-4 py-3">{e.category?.name}</td>
                                <td className="px-4 py-3">{e.description}</td>
                                <td className="px-4 py-3 text-right">{formatNaira(e.amount)}</td>
                                <td className="px-4 py-3">{e.recordedBy?.name || '—'}</td>
                                <td className="whitespace-nowrap px-4 py-3">
                                    <Link href={`/expenses/${e.id}/edit`} className="text-green-700 hover:underline">Edit</Link>
                                    {canDelete && (
                                        <button type="button" onClick={() => destroy(e)} className="ml-3 text-red-700 hover:underline">Delete</button>
                                    )}
                                </td>
                            </tr>
                        ))}
                        {expenses.data.length === 0 && (
                            <tr>
                                <td colSpan={6} className="px-4 py-10 text-center text-stone-500">
                                    {hasFilters ? 'No expenses match these filters.' : 'No expenses recorded yet.'}
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
            <Pagination links={expenses.links} />
        </AppLayout>
    );
}
