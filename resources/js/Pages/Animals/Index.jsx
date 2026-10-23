import { Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import Pagination from '../../Components/Pagination';
import StatusBadge from '../../Components/StatusBadge';
import { inputClass, primaryButtonClass, secondaryButtonClass } from '../../Components/Field';
import { STATUS_LABELS, formatDate, formatWeight } from '../../lib/format';

export default function Index({ animals, filters, animalTypes, statuses }) {
    const [form, setForm] = useState({
        search: filters.search ?? '',
        animal_type_id: filters.animal_type_id ?? '',
        status: filters.status ?? '',
    });

    const submit = (event) => {
        event.preventDefault();
        router.get('/animals', form, { preserveState: true, replace: true });
    };

    const clear = () => {
        setForm({ search: '', animal_type_id: '', status: '' });
        router.get('/animals');
    };

    const hasFilters = Object.values(filters).some((value) => value !== '' && value !== null);

    return (
        <AppLayout>
            <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 className="text-2xl font-semibold">Animals</h1>
                    <p className="text-sm text-stone-500">{animals.total} animal(s) found</p>
                </div>
                <Link href="/animals/create" className={primaryButtonClass}>
                    Add animal
                </Link>
            </div>

            <form onSubmit={submit} className="mb-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-5">
                <input
                    type="text"
                    placeholder="Search code or name"
                    value={form.search}
                    onChange={(e) => setForm({ ...form, search: e.target.value })}
                    className={`${inputClass} lg:col-span-2`}
                />
                <select
                    value={form.animal_type_id}
                    onChange={(e) => setForm({ ...form, animal_type_id: e.target.value })}
                    className={inputClass}
                >
                    <option value="">All types</option>
                    {animalTypes.map((type) => (
                        <option key={type.id} value={type.id}>
                            {type.name}{type.is_active ? '' : ' (inactive)'}
                        </option>
                    ))}
                </select>
                <select
                    value={form.status}
                    onChange={(e) => setForm({ ...form, status: e.target.value })}
                    className={inputClass}
                >
                    <option value="">All statuses</option>
                    {statuses.map((status) => (
                        <option key={status} value={status}>{STATUS_LABELS[status]}</option>
                    ))}
                </select>
                <div className="flex gap-2">
                    <button type="submit" className={secondaryButtonClass}>Filter</button>
                    {hasFilters && (
                        <button type="button" onClick={clear} className={secondaryButtonClass}>Clear</button>
                    )}
                </div>
            </form>

            <div className="overflow-x-auto rounded-lg border border-stone-200 bg-white">
                <table className="min-w-full divide-y divide-stone-200 text-sm">
                    <thead className="bg-stone-50 text-left text-stone-600">
                        <tr>
                            <th className="px-4 py-3 font-medium">Code</th>
                            <th className="px-4 py-3 font-medium">Name</th>
                            <th className="px-4 py-3 font-medium">Type</th>
                            <th className="px-4 py-3 font-medium">Acquired</th>
                            <th className="px-4 py-3 font-medium">Weight</th>
                            <th className="px-4 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-stone-100">
                        {animals.data.map((animal) => (
                            <tr key={animal.id} className="hover:bg-stone-50">
                                <td className="px-4 py-3 font-mono">
                                    <Link href={`/animals/${animal.id}`} className="text-green-700 hover:underline">
                                        {animal.animal_code}
                                    </Link>
                                </td>
                                <td className="px-4 py-3">{animal.name || '—'}</td>
                                <td className="px-4 py-3">{animal.animalType?.name}</td>
                                <td className="px-4 py-3">{formatDate(animal.acquisition_date)}</td>
                                <td className="px-4 py-3">{formatWeight(animal.weight_kg)}</td>
                                <td className="px-4 py-3"><StatusBadge status={animal.status} /></td>
                            </tr>
                        ))}
                        {animals.data.length === 0 && (
                            <tr>
                                <td colSpan={6} className="px-4 py-10 text-center text-stone-500">
                                    {hasFilters
                                        ? 'No animals match these filters.'
                                        : 'No animals registered yet. Use "Add animal" to register the first one.'}
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <Pagination links={animals.links} />
        </AppLayout>
    );
}
