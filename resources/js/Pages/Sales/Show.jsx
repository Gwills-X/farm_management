import { Link, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Field, { inputClass, primaryButtonClass, secondaryButtonClass } from '../../Components/Field';
import { PAYMENT_LABELS, PAYMENT_METHOD_LABELS, formatDate, formatNaira } from '../../lib/format';

export default function Show({ sale, paymentMethods }) {
    const outstanding = Math.max(0, Number(sale.total_amount) - Number(sale.amount_paid));
    const canPay = outstanding > 0;

    const { data, setData, post, processing, errors } = useForm({
        amount: outstanding.toFixed(2),
        paid_on: new Date().toISOString().slice(0, 10),
        payment_method: 'cash',
    });

    const submitPayment = (event) => {
        event.preventDefault();
        post(`/sales/${sale.id}/payments`, { preserveScroll: true });
    };

    return (
        <AppLayout>
            <div className="mb-6 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h1 className="font-mono text-2xl font-semibold">{sale.reference}</h1>
                    <p className="text-sm text-stone-500">{formatDate(sale.sale_date)} · {sale.buyer_name}{sale.buyer_phone ? ` · ${sale.buyer_phone}` : ''}</p>
                </div>
                <Link href="/sales" className={secondaryButtonClass}>Back to sales</Link>
            </div>

            <div className="mb-6 grid gap-4 rounded-lg border border-stone-200 bg-white p-5 sm:grid-cols-4">
                <div><div className="text-xs uppercase text-stone-500">Total</div><div className="text-lg font-semibold">{formatNaira(sale.total_amount)}</div></div>
                <div><div className="text-xs uppercase text-stone-500">Paid</div><div className="text-lg font-semibold">{formatNaira(sale.amount_paid)}</div></div>
                <div><div className="text-xs uppercase text-stone-500">Outstanding</div><div className="text-lg font-semibold">{formatNaira(outstanding)}</div></div>
                <div><div className="text-xs uppercase text-stone-500">Payment status</div><div className="text-lg font-semibold">{PAYMENT_LABELS[sale.payment_status]}</div></div>
                {sale.notes && <p className="text-sm text-stone-600 sm:col-span-4">{sale.notes}</p>}
            </div>

            <div className="mb-6 overflow-x-auto rounded-lg border border-stone-200 bg-white">
                <h2 className="px-5 pt-5 font-semibold">Animals sold</h2>
                <table className="mt-3 min-w-full divide-y divide-stone-200 text-sm">
                    <thead className="bg-stone-50 text-left text-stone-600">
                        <tr>
                            <th className="px-4 py-2 font-medium">Code</th>
                            <th className="px-4 py-2 font-medium">Name</th>
                            <th className="px-4 py-2 font-medium">Type</th>
                            <th className="px-4 py-2 font-medium text-right">Price</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-stone-100">
                        {sale.items.map((item) => (
                            <tr key={item.id}>
                                <td className="px-4 py-2 font-mono">
                                    <Link href={`/animals/${item.animal_id}`} className="text-green-700 hover:underline">{item.animal?.animal_code}</Link>
                                </td>
                                <td className="px-4 py-2">{item.animal?.name || '—'}</td>
                                <td className="px-4 py-2">{item.animal?.animalType?.name}</td>
                                <td className="px-4 py-2 text-right">{formatNaira(item.unit_price)}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            <div className="grid gap-6 lg:grid-cols-2">
                <section className="overflow-x-auto rounded-lg border border-stone-200 bg-white">
                    <h2 className="px-5 pt-5 font-semibold">Payments</h2>
                    <table className="mt-3 min-w-full divide-y divide-stone-200 text-sm">
                        <thead className="bg-stone-50 text-left text-stone-600">
                            <tr>
                                <th className="px-4 py-2 font-medium">Date</th>
                                <th className="px-4 py-2 font-medium">Method</th>
                                <th className="px-4 py-2 font-medium text-right">Amount</th>
                                <th className="px-4 py-2 font-medium">Recorded by</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-stone-100">
                            {sale.payments.map((p) => (
                                <tr key={p.id}>
                                    <td className="px-4 py-2">{formatDate(p.paid_on)}</td>
                                    <td className="px-4 py-2">{PAYMENT_METHOD_LABELS[p.payment_method] ?? '—'}</td>
                                    <td className="px-4 py-2 text-right">{formatNaira(p.amount)}</td>
                                    <td className="px-4 py-2">{p.recordedBy?.name || '—'}</td>
                                </tr>
                            ))}
                            {sale.payments.length === 0 && (
                                <tr><td colSpan={4} className="px-4 py-6 text-center text-stone-500">No payments recorded.</td></tr>
                            )}
                        </tbody>
                    </table>
                </section>

                <section className="rounded-lg border border-stone-200 bg-white p-5">
                    <h2 className="mb-4 font-semibold">Record a payment</h2>
                    {canPay ? (
                        <form onSubmit={submitPayment} className="grid gap-4 sm:grid-cols-2">
                            <Field label="Amount (₦)" error={errors.amount}>
                                <input type="number" step="0.01" min="0.01" max={outstanding} className={inputClass}
                                    value={data.amount} onChange={(e) => setData('amount', e.target.value)} />
                            </Field>
                            <Field label="Date paid" error={errors.paid_on}>
                                <input type="date" className={inputClass} value={data.paid_on}
                                    onChange={(e) => setData('paid_on', e.target.value)} />
                            </Field>
                            <Field label="Method" error={errors.payment_method}>
                                <select className={inputClass} value={data.payment_method}
                                    onChange={(e) => setData('payment_method', e.target.value)}>
                                    {paymentMethods.map((m) => <option key={m} value={m}>{PAYMENT_METHOD_LABELS[m]}</option>)}
                                </select>
                            </Field>
                            <div className="flex items-end">
                                <button type="submit" disabled={processing} className={primaryButtonClass}>Record payment</button>
                            </div>
                        </form>
                    ) : (
                        <p className="text-sm text-stone-600">This sale is fully paid.</p>
                    )}
                </section>
            </div>
        </AppLayout>
    );
}
