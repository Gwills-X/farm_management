import { Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import Pagination from '../../Components/Pagination';
import { primaryButtonClass, secondaryButtonClass, inputClass } from '../../Components/Field';

export default function Index({ types, filters, canManage }) {
    const [search, setSearch] = useState(filters.search ?? '');

    const submit = (event) => {
        event.preventDefault();
        router.get('/animal-types', { search }, { preserveState: true, replace: true });
    };

    const clear = () => {
        setSearch('');
        router.get('/animal-types');
    };

    return (
        <AppLayout>
            <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 className="text-2xl font-semibold">Animal Types</h1>
                    <p className="text-sm text-stone-500">
                        Categories of animals the farm keeps. New types appear in the Add Animal form automatically.
                    </p>
                </div>
                {canManage && (
                    <Link href="/animal-types/create" className={primaryButtonClass}>
                        New animal type
                    </Link>
                )}
            </div>

            <form onSubmit={submit} className="mb-4 flex flex-wrap gap-2">
                <input
                    type="text"
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    placeholder="Search by name"
                    className={`${inputClass} max-w-xs`}
                />
                <button type="submit" className={secondaryButtonClass}>Search</button>
                {filters.search && (
                    <button type="button" onClick={clear} className={secondaryButtonClass}>Clear</button>
                )}
            </form>

            <div className="overflow-x-auto rounded-lg border border-stone-200 bg-white">
                <table className="min-w-full divide-y divide-stone-200 text-sm">
                    <thead className="bg-stone-50 text-left text-stone-600">
                        <tr>
                            <th className="px-4 py-3 font-medium">Name</th>
                            <th className="px-4 py-3 font-medium">Code prefix</th>
                            <th className="px-4 py-3 font-medium">Animals</th>
                            <th className="px-4 py-3 font-medium">Status</th>
                            {canManage && <th className="px-4 py-3 font-medium">Actions</th>}
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-stone-100">
                        {types.data.map((type) => (
                            <tr key={type.id}>
                                <td className="px-4 py-3 font-medium">{type.name}</td>
                                <td className="px-4 py-3 font-mono">{type.code_prefix}</td>
                                <td className="px-4 py-3">{type.animals_count}</td>
                                <td className="px-4 py-3">
                                    {type.is_active ? (
                                        <span className="text-green-700">Active</span>
                                    ) : (
                                        <span className="text-stone-500">Inactive</span>
                                    )}
                                </td>
                                {canManage && (
                                    <td className="px-4 py-3">
                                        <Link href={`/animal-types/${type.id}/edit`} className="text-green-700 hover:underline">
                                            Edit
                                        </Link>
                                    </td>
                                )}
                            </tr>
                        ))}
                        {types.data.length === 0 && (
                            <tr>
                                <td colSpan={canManage ? 5 : 4} className="px-4 py-10 text-center text-stone-500">
                                    No animal types found.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <Pagination links={types.links} />
        </AppLayout>
    );
}
