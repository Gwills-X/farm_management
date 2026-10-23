import { Link, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Field, { inputClass, primaryButtonClass, secondaryButtonClass } from '../../Components/Field';
import { toDateTimeInput } from '../../lib/format';

const UNIT_LABELS = { kg: 'kg', g: 'g', litre: 'litre', bag: 'bag', other: 'other' };

export default function Form({ animal, record, units }) {
    const isEdit = Boolean(record);

    const { data, setData, post, put, processing, errors } = useForm({
        fed_at: toDateTimeInput(record?.fed_at) || toDateTimeInput(new Date()),
        feed_type: record?.feed_type ?? '',
        quantity: record?.quantity ?? '',
        unit: record?.unit ?? 'kg',
        notes: record?.notes ?? '',
    });

    const submit = (event) => {
        event.preventDefault();
        if (isEdit) {
            put(`/feeding-records/${record.id}`);
        } else {
            post(`/animals/${animal.id}/feeding-records`);
        }
    };

    return (
        <AppLayout>
            <div className="mb-6">
                <h1 className="text-2xl font-semibold">{isEdit ? 'Edit feeding record' : 'Record feeding'}</h1>
                <p className="text-sm text-stone-500">
                    {animal.animal_code}{animal.name ? ` · ${animal.name}` : ''} · {animal.animalType?.name}
                </p>
            </div>

            <form onSubmit={submit} className="max-w-3xl space-y-6 rounded-lg border border-stone-200 bg-white p-6">
                <div className="grid gap-5 sm:grid-cols-2">
                    <Field label="Date and time fed" error={errors.fed_at}>
                        <input type="datetime-local" className={inputClass} value={data.fed_at}
                            onChange={(e) => setData('fed_at', e.target.value)} />
                    </Field>

                    <Field label="Feed type" error={errors.feed_type}>
                        <input className={inputClass} value={data.feed_type} placeholder="e.g. Maize bran"
                            onChange={(e) => setData('feed_type', e.target.value)} />
                    </Field>

                    <Field label="Quantity" error={errors.quantity}>
                        <input type="number" step="0.01" min="0.01" className={inputClass} value={data.quantity}
                            onChange={(e) => setData('quantity', e.target.value)} />
                    </Field>

                    <Field label="Unit" error={errors.unit}>
                        <select className={inputClass} value={data.unit} onChange={(e) => setData('unit', e.target.value)}>
                            {units.map((u) => <option key={u} value={u}>{UNIT_LABELS[u]}</option>)}
                        </select>
                    </Field>
                </div>

                <Field label="Notes (optional)" error={errors.notes}>
                    <textarea rows={2} className={inputClass} value={data.notes}
                        onChange={(e) => setData('notes', e.target.value)} />
                </Field>

                <div className="flex gap-3">
                    <button type="submit" disabled={processing} className={primaryButtonClass}>
                        {isEdit ? 'Save changes' : 'Save feeding record'}
                    </button>
                    <Link href={`/animals/${animal.id}`} className={secondaryButtonClass}>Cancel</Link>
                </div>
            </form>
        </AppLayout>
    );
}
