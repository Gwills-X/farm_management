import { Link, router, usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import StatusBadge from '../../Components/StatusBadge';
import { secondaryButtonClass, primaryButtonClass } from '../../Components/Field';
import {
    ACQUISITION_LABELS,
    HEALTH_LABELS,
    SEX_LABELS,
    formatDate,
    formatWeight,
} from '../../lib/format';

function Detail({ label, children }) {
    return (
        <div>
            <dt className="text-xs uppercase tracking-wide text-stone-500">{label}</dt>
            <dd className="mt-0.5 text-sm text-stone-800">{children || '—'}</dd>
        </div>
    );
}

function EmptyRow({ colSpan, text }) {
    return (
        <tr>
            <td colSpan={colSpan} className="px-4 py-6 text-center text-sm text-stone-500">{text}</td>
        </tr>
    );
}

export default function Show({ animal, healthRecords, feedingRecords, hasRecords }) {
    const { auth } = usePage().props;
    const isAdmin = auth.user?.role === 'admin';
    const canFeed = !['sold', 'deceased'].includes(animal.status);

    const destroy = () => {
        if (window.confirm(`Delete ${animal.animal_code}? This cannot be undone.`)) {
            router.delete(`/animals/${animal.id}`);
        }
    };

    return (
        <AppLayout>
            <div className="mb-6 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div className="flex items-center gap-3">
                        <h1 className="font-mono text-2xl font-semibold">{animal.animal_code}</h1>
                        <StatusBadge status={animal.status} />
                    </div>
                    <p className="text-sm text-stone-500">
                        {animal.name ? `${animal.name} · ` : ''}{animal.animalType?.name}
                    </p>
                </div>
                <div className="flex flex-wrap gap-2">
                    <Link href={`/animals/${animal.id}/edit`} className={secondaryButtonClass}>Edit</Link>
                    <Link href={`/animals/${animal.id}/health-records/create`} className={secondaryButtonClass}>
                        Record health
                    </Link>
                    {canFeed && (
                        <Link href={`/animals/${animal.id}/feeding-records/create`} className={secondaryButtonClass}>
                            Record feeding
                        </Link>
                    )}
                    <Link href="/animals" className={secondaryButtonClass}>Back to list</Link>
                    {isAdmin && !hasRecords && (
                        <button type="button" onClick={destroy} className="rounded-md border border-red-300 px-4 py-2 text-sm text-red-700 hover:bg-red-50">
                            Delete
                        </button>
                    )}
                </div>
            </div>

            <div className="mb-6 grid gap-6 lg:grid-cols-2">
                <section className="rounded-lg border border-stone-200 bg-white p-5">
                    <h2 className="mb-4 font-semibold">Details</h2>
                    <dl className="grid grid-cols-2 gap-4">
                        <Detail label="Sex">{SEX_LABELS[animal.sex]}</Detail>
                        <Detail label="Breed">{animal.breed}</Detail>
                        <Detail label="Date of birth">{formatDate(animal.date_of_birth)}</Detail>
                        <Detail label="Weight">{formatWeight(animal.weight_kg)}</Detail>
                        <Detail label="Acquisition">{ACQUISITION_LABELS[animal.acquisition_type]}</Detail>
                        <Detail label="Acquired on">{formatDate(animal.acquisition_date)}</Detail>
                        <Detail label="Source">{animal.source}</Detail>
                        <Detail label="Notes">{animal.notes}</Detail>
                    </dl>
                </section>

                <section className="rounded-lg border border-stone-200 bg-white p-5">
                    <h2 className="mb-4 font-semibold">Purchase and sale</h2>
                    {animal.purchaseItem ? (
                        <dl className="grid grid-cols-2 gap-4">
                            <Detail label="Purchase reference">
                                <Link href={`/purchases/${animal.purchaseItem.purchase_id}`} className="text-green-700 hover:underline">
                                    {animal.purchaseItem.purchase?.reference}
                                </Link>
                            </Detail>
                            <Detail label="Purchase date">{formatDate(animal.purchaseItem.purchase?.purchase_date)}</Detail>
                            <Detail label="Seller">{animal.purchaseItem.purchase?.seller_name}</Detail>
                        </dl>
                    ) : (
                        <p className="text-sm text-stone-500">Not linked to a purchase record.</p>
                    )}

                    <div className="mt-5 border-t border-stone-100 pt-5">
                        {animal.saleItem ? (
                            <dl className="grid grid-cols-2 gap-4">
                                <Detail label="Sale reference">
                                    <Link href={`/sales/${animal.saleItem.sale_id}`} className="text-green-700 hover:underline">
                                        {animal.saleItem.sale?.reference}
                                    </Link>
                                </Detail>
                                <Detail label="Sale date">{formatDate(animal.saleItem.sale?.sale_date)}</Detail>
                                <Detail label="Buyer">{animal.saleItem.sale?.buyer_name}</Detail>
                                <Detail label="Payment">{animal.saleItem.sale?.payment_status}</Detail>
                            </dl>
                        ) : (
                            <p className="text-sm text-stone-500">Not sold.</p>
                        )}
                    </div>
                </section>
            </div>

            <section className="mb-6 overflow-x-auto rounded-lg border border-stone-200 bg-white">
                <div className="flex items-center justify-between px-5 pt-5">
                    <h2 className="font-semibold">Recent health observations</h2>
                    <Link href="/health-records" className="text-sm text-green-700 hover:underline">All health records</Link>
                </div>
                <table className="mt-3 min-w-full divide-y divide-stone-200 text-sm">
                    <thead className="bg-stone-50 text-left text-stone-600">
                        <tr>
                            <th className="px-4 py-2 font-medium">Date</th>
                            <th className="px-4 py-2 font-medium">Condition</th>
                            <th className="px-4 py-2 font-medium">Status</th>
                            <th className="px-4 py-2 font-medium">Recorded by</th>
                            <th className="px-4 py-2 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-stone-100">
                        {healthRecords.map((record) => (
                            <tr key={record.id}>
                                <td className="px-4 py-2">{formatDate(record.observation_date)}</td>
                                <td className="px-4 py-2">{record.observed_condition}</td>
                                <td className="px-4 py-2">{HEALTH_LABELS[record.health_status]}</td>
                                <td className="px-4 py-2">{record.recordedBy?.name || '—'}</td>
                                <td className="px-4 py-2">
                                    <Link href={`/health-records/${record.id}/edit`} className="text-green-700 hover:underline">Edit</Link>
                                </td>
                            </tr>
                        ))}
                        {healthRecords.length === 0 && <EmptyRow colSpan={5} text="No health observations recorded yet." />}
                    </tbody>
                </table>
            </section>

            <section className="overflow-x-auto rounded-lg border border-stone-200 bg-white">
                <div className="flex items-center justify-between px-5 pt-5">
                    <h2 className="font-semibold">Recent feeding</h2>
                    <Link href="/feeding-records" className="text-sm text-green-700 hover:underline">All feeding records</Link>
                </div>
                <table className="mt-3 min-w-full divide-y divide-stone-200 text-sm">
                    <thead className="bg-stone-50 text-left text-stone-600">
                        <tr>
                            <th className="px-4 py-2 font-medium">Date</th>
                            <th className="px-4 py-2 font-medium">Feed</th>
                            <th className="px-4 py-2 font-medium">Quantity</th>
                            <th className="px-4 py-2 font-medium">Recorded by</th>
                            <th className="px-4 py-2 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-stone-100">
                        {feedingRecords.map((record) => (
                            <tr key={record.id}>
                                <td className="px-4 py-2">{formatDate(record.fed_at)}</td>
                                <td className="px-4 py-2">{record.feed_type}</td>
                                <td className="px-4 py-2">{`${record.quantity} ${record.unit}`}</td>
                                <td className="px-4 py-2">{record.recordedBy?.name || '—'}</td>
                                <td className="px-4 py-2">
                                    <Link href={`/feeding-records/${record.id}/edit`} className="text-green-700 hover:underline">Edit</Link>
                                </td>
                            </tr>
                        ))}
                        {feedingRecords.length === 0 && <EmptyRow colSpan={5} text="No feeding records yet." />}
                    </tbody>
                </table>
            </section>
        </AppLayout>
    );
}
