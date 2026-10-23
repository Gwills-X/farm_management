import { Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import Pagination from '../../Components/Pagination';
import { inputClass, primaryButtonClass, secondaryButtonClass } from '../../Components/Field';
import { formatDateTime } from '../../lib/format';

export default function Index({ records, filters, animalTypes }) {
    const [form, setForm] = useState({
        search: filters.search ?? '',
        animal_type_id: filters.animal_type_id ?? '',
        from: filters.from ?? '',
        to: filters.to ?? '',
    });

    const submit = (event) => {
        event.preventDefault();
        router.get('/feeding-records', form, { preserveState: true, replace: true });
    };

    const clear = () => {
        setForm({ search: '', animal_type_id: '', from: '', to: '' });
        router.get('/feeding-records');
    };

    const hasFilters = Object.values(filters).some((value) => value);

    return (
        <AppLayout>
            <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 className="text-2xl font-semibold">Feeding records</h1>
                    <p className="text-sm text-stone-500">{records.total} record(s)</p>
                </div>
                <p className="text-sm text-stone-500">To add a record, open an animal and choose "Record feeding".</p>
            </div>

            <form onSubmit={submit} className="mb-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-5">
                <input className={`${inputClass} lg:col-span-2`} placeholder="Search animal code or name"
                    value={form.search} onChange={(e) => setForm({ ...form, search: e.target.value })} />
                <select className={inputClass} value={form.animal_type_id}
                    onChange={(e) => setForm({ ...form, animal_type_id: e.target.value })}>
                    <option value="">All types</option>
                    {animalTypes.map((t) => <option key={t.id} value={t.id}>{t.name}</option>)}
                </select>
                <input type="date" className={inputClass} value={form.from} onChange={(e) => setForm({ ...form, from: e.target.value })} />
                <input type="date" className={inputClass} value={form.to} onChange={(e) => setForm({ ...form, to: e.target.value })} />
                <div className="flex gap-2 sm:col-span-2 lg:col-span-5">
                    <button type="submit" className={primaryButtonClass}>Filter</button>
                    {hasFilters && <button type="button" onClick={clear} className={secondaryButtonClass}>Clear</button>}
                </div>
            </form>

            <div className="overflow-x-auto rounded-lg border border-stone-200 bg-white">
                <table className="min-w-full divide-y divide-stone-200 text-sm">
                    <thead className="bg-stone-50 text-left text-stone-600">
                        <tr>
                            <th className="px-4 py-3 font-medium">Fed at</th>
                            <th className="px-4 py-3 font-medium">Animal</th>
                            <th className="px-4 py-3 font-medium">Feed</th>
                            <th className="px-4 py-3 font-medium">Quantity</th>
                            <th className="px-4 py-3 font-medium">Recorded by</th>
                            <th className="px-4 py-3 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-stone-100">
                        {records.data.map((record) => (
                            <tr key={record.id}>
                                <td className="px-4 py-3">{formatDateTime(record.fed_at)}</td>
                                <td className="px-4 py-3">
                                    <Link href={`/animals/${record.animal_id}`} className="font-mono text-green-700 hover:underline">
                                        {record.animal?.animal_code}
                                    </Link>
                                    <span className="ml-2 text-stone-500">{record.animal?.animalType?.name}</span>
                                </td>
                                <td className="px-4 py-3">{record.feed_type}</td>
                                <td className="px-4 py-3">{`${record.quantity} ${record.unit}`}</td>
                                <td className="px-4 py-3">{record.recordedBy?.name || '—'}</td>
                                <td className="px-4 py-3">
                                    <Link href={`/feeding-records/${record.id}/edit`} className="text-green-700 hover:underline">Edit</Link>
                                </td>
                            </tr>
                        ))}
                        {records.data.length === 0 && (
                            <tr>
                                <td colSpan={6} className="px-4 py-10 text-center text-stone-500">
                                    {hasFilters ? 'No records match these filters.' : 'No feeding records yet.'}
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
            <Pagination links={records.links} />
        </AppLayout>
    );
}
