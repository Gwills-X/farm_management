import { Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import Pagination from '../../Components/Pagination';
import { inputClass, primaryButtonClass, secondaryButtonClass } from '../../Components/Field';
import { HEALTH_LABELS, formatDate } from '../../lib/format';

export default function Index({ records, filters, statuses }) {
    const [form, setForm] = useState({
        search: filters.search ?? '',
        status: filters.status ?? '',
        from: filters.from ?? '',
        to: filters.to ?? '',
    });

    const submit = (event) => {
        event.preventDefault();
        router.get('/health-records', form, { preserveState: true, replace: true });
    };

    const clear = () => {
        setForm({ search: '', status: '', from: '', to: '' });
        router.get('/health-records');
    };

    const hasFilters = Object.values(filters).some((value) => value);

    return (
        <AppLayout>
            <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 className="text-2xl font-semibold">Health records</h1>
                    <p className="text-sm text-stone-500">{records.total} observation(s)</p>
                </div>
                <p className="text-sm text-stone-500">To add a record, open an animal and choose "Record health".</p>
            </div>

            <form onSubmit={submit} className="mb-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-5">
                <input
                    className={`${inputClass} lg:col-span-2`}
                    placeholder="Search animal code or name"
                    value={form.search}
                    onChange={(e) => setForm({ ...form, search: e.target.value })}
                />
                <select className={inputClass} value={form.status} onChange={(e) => setForm({ ...form, status: e.target.value })}>
                    <option value="">All statuses</option>
                    {statuses.map((s) => <option key={s} value={s}>{HEALTH_LABELS[s]}</option>)}
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
                            <th className="px-4 py-3 font-medium">Date</th>
                            <th className="px-4 py-3 font-medium">Animal</th>
                            <th className="px-4 py-3 font-medium">Condition</th>
                            <th className="px-4 py-3 font-medium">Status</th>
                            <th className="px-4 py-3 font-medium">Follow-up</th>
                            <th className="px-4 py-3 font-medium">Recorded by</th>
                            <th className="px-4 py-3 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-stone-100">
                        {records.data.map((record) => (
                            <tr key={record.id}>
                                <td className="px-4 py-3">{formatDate(record.observation_date)}</td>
                                <td className="px-4 py-3">
                                    <Link href={`/animals/${record.animal_id}`} className="font-mono text-green-700 hover:underline">
                                        {record.animal?.animal_code}
                                    </Link>
                                </td>
                                <td className="px-4 py-3">{record.observed_condition}</td>
                                <td className="px-4 py-3">{HEALTH_LABELS[record.health_status]}</td>
                                <td className="px-4 py-3">{formatDate(record.follow_up_date)}</td>
                                <td className="px-4 py-3">{record.recordedBy?.name || '—'}</td>
                                <td className="px-4 py-3">
                                    <Link href={`/health-records/${record.id}/edit`} className="text-green-700 hover:underline">Edit</Link>
                                </td>
                            </tr>
                        ))}
                        {records.data.length === 0 && (
                            <tr>
                                <td colSpan={7} className="px-4 py-10 text-center text-stone-500">
                                    {hasFilters ? 'No records match these filters.' : 'No health observations yet.'}
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
