import { Link, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Field, { inputClass, primaryButtonClass, secondaryButtonClass } from '../../Components/Field';
import { HEALTH_LABELS, toDateInput } from '../../lib/format';

export default function Form({ animal, record, statuses }) {
    const isEdit = Boolean(record);
    const today = new Date().toISOString().slice(0, 10);

    const { data, setData, post, put, processing, errors } = useForm({
        observation_date: toDateInput(record?.observation_date) || today,
        observed_condition: record?.observed_condition ?? '',
        health_status: record?.health_status ?? 'healthy',
        treatment: record?.treatment ?? '',
        medication: record?.medication ?? '',
        veterinary_professional: record?.veterinary_professional ?? '',
        follow_up_date: toDateInput(record?.follow_up_date),
        notes: record?.notes ?? '',
    });

    const submit = (event) => {
        event.preventDefault();
        if (isEdit) {
            put(`/health-records/${record.id}`);
        } else {
            post(`/animals/${animal.id}/health-records`);
        }
    };

    return (
        <AppLayout>
            <div className="mb-6">
                <h1 className="text-2xl font-semibold">{isEdit ? 'Edit health observation' : 'Record health observation'}</h1>
                <p className="text-sm text-stone-500">
                    {animal.animal_code}{animal.name ? ` · ${animal.name}` : ''} · {animal.animalType?.name}
                </p>
            </div>

            <form onSubmit={submit} className="max-w-3xl space-y-6 rounded-lg border border-stone-200 bg-white p-6">
                <div className="grid gap-5 sm:grid-cols-2">
                    <Field label="Observation date" error={errors.observation_date}>
                        <input type="date" className={inputClass} value={data.observation_date}
                            onChange={(e) => setData('observation_date', e.target.value)} />
                    </Field>

                    <Field label="Health status" error={errors.health_status}>
                        <select className={inputClass} value={data.health_status}
                            onChange={(e) => setData('health_status', e.target.value)}>
                            {statuses.map((s) => <option key={s} value={s}>{HEALTH_LABELS[s]}</option>)}
                        </select>
                    </Field>

                    <Field label="Follow-up date (optional)" error={errors.follow_up_date}>
                        <input type="date" className={inputClass} value={data.follow_up_date}
                            onChange={(e) => setData('follow_up_date', e.target.value)} />
                    </Field>

                    <Field label="Veterinary professional (optional)" error={errors.veterinary_professional}>
                        <input className={inputClass} value={data.veterinary_professional}
                            onChange={(e) => setData('veterinary_professional', e.target.value)} />
                    </Field>
                </div>

                <Field label="Observed condition or symptoms" error={errors.observed_condition}>
                    <textarea rows={3} className={inputClass} value={data.observed_condition}
                        onChange={(e) => setData('observed_condition', e.target.value)} />
                </Field>

                <Field label="Treatment or action taken (optional)" error={errors.treatment}>
                    <textarea rows={2} className={inputClass} value={data.treatment}
                        onChange={(e) => setData('treatment', e.target.value)} />
                </Field>

                <Field label="Medication (optional)" error={errors.medication}>
                    <input className={inputClass} value={data.medication}
                        onChange={(e) => setData('medication', e.target.value)} />
                </Field>

                <Field label="Notes (optional)" error={errors.notes}>
                    <textarea rows={2} className={inputClass} value={data.notes}
                        onChange={(e) => setData('notes', e.target.value)} />
                </Field>

                <div className="flex gap-3">
                    <button type="submit" disabled={processing} className={primaryButtonClass}>
                        {isEdit ? 'Save changes' : 'Save observation'}
                    </button>
                    <Link href={`/animals/${animal.id}`} className={secondaryButtonClass}>Cancel</Link>
                </div>
            </form>
        </AppLayout>
    );
}
