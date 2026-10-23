import { Link, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Field, { inputClass, primaryButtonClass, secondaryButtonClass } from '../../Components/Field';
import { PAYMENT_METHOD_LABELS, formatNaira } from '../../lib/format';

export default function Create({ animals, paymentMethods }) {
    const { data, setData, post, processing, errors } = useForm({
        sale_date: new Date().toISOString().slice(0, 10),
        buyer_name: '',
        buyer_phone: '',
        payment_method: 'cash',
        amount_paid: '',
        notes: '',
        items: [{ animal_id: '', unit_price: '' }],
    });

    const updateItem = (index, field, value) => {
        setData('items', data.items.map((item, i) => (i === index ? { ...item, [field]: value } : item)));
    };

    const addItem = () => setData('items', [...data.items, { animal_id: '', unit_price: '' }]);

    const removeItem = (index) => {
        if (data.items.length === 1) return;
        setData('items', data.items.filter((_, i) => i !== index));
    };

    const total = data.items.reduce((sum, item) => sum + (Number(item.unit_price) || 0), 0);
    const paid = Number(data.amount_paid) || 0;
    const outstanding = Math.max(0, total - paid);

    const submit = (event) => {
        event.preventDefault();
        post('/sales');
    };

    if (animals.length === 0) {
        return (
            <AppLayout>
                <h1 className="mb-4 text-2xl font-semibold">Record sale</h1>
                <div className="max-w-xl rounded-lg border border-amber-200 bg-amber-50 p-6">
                    <p className="text-amber-900">There are no active animals available for sale.</p>
                    <Link href="/animals" className={`${secondaryButtonClass} mt-4`}>View animals</Link>
                </div>
            </AppLayout>
        );
    }

    return (
        <AppLayout>
            <div className="mb-6">
                <h1 className="text-2xl font-semibold">Record sale</h1>
                <p className="text-sm text-stone-500">
                    Only active animals can be sold. Each selected animal is marked as sold when you save.
                </p>
            </div>

            <form onSubmit={submit} className="space-y-6">
                <div className="grid gap-5 rounded-lg border border-stone-200 bg-white p-6 sm:grid-cols-3">
                    <Field label="Sale date" error={errors.sale_date}>
                        <input type="date" className={inputClass} value={data.sale_date}
                            onChange={(e) => setData('sale_date', e.target.value)} />
                    </Field>
                    <Field label="Buyer name" error={errors.buyer_name}>
                        <input className={inputClass} value={data.buyer_name}
                            onChange={(e) => setData('buyer_name', e.target.value)} />
                    </Field>
                    <Field label="Buyer phone (optional)" error={errors.buyer_phone}>
                        <input className={inputClass} value={data.buyer_phone}
                            onChange={(e) => setData('buyer_phone', e.target.value)} />
                    </Field>
                </div>

                {errors.items && <p className="text-sm text-red-600">{errors.items}</p>}

                <div className="space-y-3">
                    {data.items.map((item, index) => (
                        <div key={index} className="grid items-end gap-4 rounded-lg border border-stone-200 bg-white p-4 sm:grid-cols-[2fr_1fr_auto]">
                            <Field label="Animal" error={errors[`items.${index}.animal_id`]}>
                                <select className={inputClass} value={item.animal_id}
                                    onChange={(e) => updateItem(index, 'animal_id', e.target.value)}>
                                    <option value="">Select an animal</option>
                                    {animals.map((a) => (
                                        <option key={a.id} value={a.id}>
                                            {a.animal_code}{a.name ? ` · ${a.name}` : ''} ({a.animalType?.name})
                                        </option>
                                    ))}
                                </select>
                            </Field>
                            <Field label="Sale price (₦)" error={errors[`items.${index}.unit_price`]}>
                                <input type="number" step="0.01" min="0" className={inputClass} value={item.unit_price}
                                    onChange={(e) => updateItem(index, 'unit_price', e.target.value)} />
                            </Field>
                            <button type="button" onClick={() => removeItem(index)}
                                disabled={data.items.length === 1}
                                className="mb-1 text-sm text-red-700 hover:underline disabled:opacity-40">
                                Remove
                            </button>
                        </div>
                    ))}
                </div>
                <button type="button" onClick={addItem} className={secondaryButtonClass}>Add another animal</button>

                <div className="grid gap-5 rounded-lg border border-stone-200 bg-white p-6 sm:grid-cols-3">
                    <Field label="Amount paid now (₦)" error={errors.amount_paid} hint="Leave blank if nothing has been paid yet.">
                        <input type="number" step="0.01" min="0" className={inputClass} value={data.amount_paid}
                            onChange={(e) => setData('amount_paid', e.target.value)} />
                    </Field>
                    <Field label="Payment method" error={errors.payment_method}>
                        <select className={inputClass} value={data.payment_method}
                            onChange={(e) => setData('payment_method', e.target.value)}>
                            {paymentMethods.map((m) => <option key={m} value={m}>{PAYMENT_METHOD_LABELS[m]}</option>)}
                        </select>
                    </Field>
                    <Field label="Notes (optional)" error={errors.notes}>
                        <input className={inputClass} value={data.notes}
                            onChange={(e) => setData('notes', e.target.value)} />
                    </Field>
                </div>

                <div className="grid gap-4 rounded-lg border border-stone-200 bg-white p-5 sm:grid-cols-3">
                    <div><div className="text-sm text-stone-500">Total</div><div className="text-lg font-semibold">{formatNaira(total)}</div></div>
                    <div><div className="text-sm text-stone-500">Paid now</div><div className="text-lg font-semibold">{formatNaira(paid)}</div></div>
                    <div><div className="text-sm text-stone-500">Outstanding</div><div className="text-lg font-semibold">{formatNaira(outstanding)}</div></div>
                </div>

                <div className="flex gap-3">
                    <button type="submit" disabled={processing} className={primaryButtonClass}>Save sale</button>
                    <Link href="/sales" className={secondaryButtonClass}>Cancel</Link>
                </div>
            </form>
        </AppLayout>
    );
}
