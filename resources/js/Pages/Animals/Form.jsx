import { Link, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Field, { inputClass, primaryButtonClass, secondaryButtonClass } from '../../Components/Field';
import {
    ACQUISITION_LABELS,
    SEX_LABELS,
    STATUS_LABELS,
    toDateInput,
} from '../../lib/format';

export default function Form({ animal, animalTypes, statuses }) {
    const isEdit = Boolean(animal);
    const isSold = isEdit && animal.status === 'sold';

    const { data, setData, post, put, processing, errors } = useForm({
        animal_type_id: animal?.animal_type_id ?? (animalTypes[0]?.id ?? ''),
        name: animal?.name ?? '',
        sex: animal?.sex ?? 'unknown',
        breed: animal?.breed ?? '',
        date_of_birth: toDateInput(animal?.date_of_birth),
        acquisition_type: animal?.acquisition_type ?? 'born_on_farm',
        acquisition_date: toDateInput(animal?.acquisition_date) || new Date().toISOString().slice(0, 10),
        source: animal?.source ?? '',
        weight_kg: animal?.weight_kg ?? '',
        status: animal?.status ?? 'active',
        notes: animal?.notes ?? '',
    });

    const submit = (event) => {
        event.preventDefault();
        if (isEdit) {
            put(`/animals/${animal.id}`);
        } else {
            post('/animals');
        }
    };

    // Creating an animal requires at least one active animal type.
    if (!isEdit && animalTypes.length === 0) {
        return (
            <AppLayout>
                <h1 className="mb-4 text-2xl font-semibold">Add animal</h1>
                <div className="max-w-xl rounded-lg border border-amber-200 bg-amber-50 p-6">
                    <p className="text-amber-900">
                        There are no active animal types yet. Create one first, then come back to register animals.
                    </p>
                    <Link href="/animal-types/create" className={`${primaryButtonClass} mt-4`}>
                        Create an animal type
                    </Link>
                </div>
            </AppLayout>
        );
    }

    return (
        <AppLayout>
            <div className="mb-6">
                <h1 className="text-2xl font-semibold">
                    {isEdit ? `Edit ${animal.animal_code}` : 'Add animal'}
                </h1>
                <p className="text-sm text-stone-500">
                    {isEdit
                        ? 'The animal code and type cannot be changed.'
                        : 'The animal code is generated automatically when you save.'}
                </p>
            </div>

            <form onSubmit={submit} className="max-w-3xl space-y-6 rounded-lg border border-stone-200 bg-white p-6">
                <div className="grid gap-5 sm:grid-cols-2">
                    <Field label="Animal type" error={errors.animal_type_id}>
                        {isEdit ? (
                            <input
                                className={inputClass}
                                value={animal.animalType?.name ?? ''}
                                disabled
                            />
                        ) : (
                            <select
                                className={inputClass}
                                value={data.animal_type_id}
                                onChange={(e) => setData('animal_type_id', e.target.value)}
                            >
                                {animalTypes.map((type) => (
                                    <option key={type.id} value={type.id}>{type.name}</option>
                                ))}
                            </select>
                        )}
                    </Field>

                    <Field label="Name (optional)" error={errors.name}>
                        <input
                            className={inputClass}
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                        />
                    </Field>

                    <Field label="Sex" error={errors.sex}>
                        <select className={inputClass} value={data.sex} onChange={(e) => setData('sex', e.target.value)}>
                            {Object.entries(SEX_LABELS).map(([value, label]) => (
                                <option key={value} value={value}>{label}</option>
                            ))}
                        </select>
                    </Field>

                    <Field label="Breed" error={errors.breed}>
                        <input
                            className={inputClass}
                            value={data.breed}
                            onChange={(e) => setData('breed', e.target.value)}
                        />
                    </Field>

                    <Field label="Date of birth" error={errors.date_of_birth}>
                        <input
                            type="date"
                            className={inputClass}
                            value={data.date_of_birth}
                            onChange={(e) => setData('date_of_birth', e.target.value)}
                        />
                    </Field>

                    <Field label="Weight (kg)" error={errors.weight_kg}>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            className={inputClass}
                            value={data.weight_kg}
                            onChange={(e) => setData('weight_kg', e.target.value)}
                        />
                    </Field>

                    <Field label="How acquired" error={errors.acquisition_type}>
                        <select
                            className={inputClass}
                            value={data.acquisition_type}
                            onChange={(e) => setData('acquisition_type', e.target.value)}
                        >
                            {Object.entries(ACQUISITION_LABELS).map(([value, label]) => (
                                <option key={value} value={value}>{label}</option>
                            ))}
                        </select>
                    </Field>

                    <Field label="Acquisition date" error={errors.acquisition_date}>
                        <input
                            type="date"
                            className={inputClass}
                            value={data.acquisition_date}
                            onChange={(e) => setData('acquisition_date', e.target.value)}
                        />
                    </Field>

                    <Field label="Source / seller (optional)" error={errors.source}>
                        <input
                            className={inputClass}
                            value={data.source}
                            onChange={(e) => setData('source', e.target.value)}
                        />
                    </Field>

                    {isEdit && (
                        <Field
                            label="Status"
                            error={errors.status}
                            hint={isSold ? 'Sold animals keep their status. Record changes through sales.' : undefined}
                        >
                            <select
                                className={inputClass}
                                value={data.status}
                                disabled={isSold}
                                onChange={(e) => setData('status', e.target.value)}
                            >
                                {statuses.map((status) => (
                                    <option key={status} value={status} disabled={status === 'sold'}>
                                        {STATUS_LABELS[status]}
                                    </option>
                                ))}
                            </select>
                        </Field>
                    )}
                </div>

                <Field label="Notes" error={errors.notes}>
                    <textarea
                        rows={3}
                        className={inputClass}
                        value={data.notes}
                        onChange={(e) => setData('notes', e.target.value)}
                    />
                </Field>

                <div className="flex gap-3">
                    <button type="submit" disabled={processing} className={primaryButtonClass}>
                        {isEdit ? 'Save changes' : 'Register animal'}
                    </button>
                    <Link
                        href={isEdit ? `/animals/${animal.id}` : '/animals'}
                        className={secondaryButtonClass}
                    >
                        Cancel
                    </Link>
                </div>
            </form>
        </AppLayout>
    );
}
