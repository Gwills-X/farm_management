import { Link } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import StatusBadge from '../../Components/StatusBadge';
import { secondaryButtonClass } from '../../Components/Field';
import { SEX_LABELS, formatDate, formatNaira } from '../../lib/format';

export default function Show({ purchase }) {
    return (
        <AppLayout>
            <div className="mb-6 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h1 className="font-mono text-2xl font-semibold">{purchase.reference}</h1>
                    <p className="text-sm text-stone-500">
                        {formatDate(purchase.purchase_date)} · {purchase.seller_name || 'Seller not recorded'}
                    </p>
                </div>
                <Link href="/purchases" className={secondaryButtonClass}>Back to purchases</Link>
            </div>

            <div className="mb-6 grid gap-4 rounded-lg border border-stone-200 bg-white p-5 sm:grid-cols-3">
                <div>
                    <div className="text-xs uppercase text-stone-500">Total cost</div>
                    <div className="text-lg font-semibold">{formatNaira(purchase.total_cost)}</div>
                </div>
                <div>
                    <div className="text-xs uppercase text-stone-500">Animals</div>
                    <div className="text-lg font-semibold">{purchase.items.length}</div>
                </div>
                <div>
                    <div className="text-xs uppercase text-stone-500">Recorded by</div>
                    <div className="text-lg font-semibold">{purchase.recordedBy?.name || '—'}</div>
                </div>
                {purchase.notes && <p className="sm:col-span-3 text-sm text-stone-600">{purchase.notes}</p>}
            </div>

            <div className="overflow-x-auto rounded-lg border border-stone-200 bg-white">
                <table className="min-w-full divide-y divide-stone-200 text-sm">
                    <thead className="bg-stone-50 text-left text-stone-600">
                        <tr>
                            <th className="px-4 py-3 font-medium">Code</th>
                            <th className="px-4 py-3 font-medium">Name</th>
                            <th className="px-4 py-3 font-medium">Type</th>
                            <th className="px-4 py-3 font-medium">Sex</th>
                            <th className="px-4 py-3 font-medium text-right">Unit cost</th>
                            <th className="px-4 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-stone-100">
                        {purchase.items.map((item) => (
                            <tr key={item.id}>
                                <td className="px-4 py-3 font-mono">
                                    <Link href={`/animals/${item.animal_id}`} className="text-green-700 hover:underline">
                                        {item.animal?.animal_code}
                                    </Link>
                                </td>
                                <td className="px-4 py-3">{item.animal?.name || '—'}</td>
                                <td className="px-4 py-3">{item.animal?.animalType?.name}</td>
                                <td className="px-4 py-3">{SEX_LABELS[item.animal?.sex] ?? '—'}</td>
                                <td className="px-4 py-3 text-right">{formatNaira(item.unit_cost)}</td>
                                <td className="px-4 py-3"><StatusBadge status={item.animal?.status} /></td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AppLayout>
    );
}
