import { Link, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Field, { inputClass, primaryButtonClass, secondaryButtonClass } from '../../Components/Field';

export default function Form({ type }) {
    const isEdit = Boolean(type);
    const prefixLocked = isEdit && type.animals_count > 0;

    const { data, setData, post, put, processing, errors } = useForm({
        name: type?.name ?? '',
        code_prefix: type?.code_prefix ?? '',
        description: type?.description ?? '',
        is_active: type?.is_active ?? true,
    });

    const submit = (event) => {
        event.preventDefault();
        if (isEdit) {
            put(`/animal-types/${type.id}`);
        } else {
            post('/animal-types');
        }
    };

    return (
        <AppLayout>
            <div className="mb-6">
                <h1 className="text-2xl font-semibold">{isEdit ? `Edit ${type.name}` : 'New animal type'}</h1>
                <p className="text-sm text-stone-500">
                    The code prefix is used in animal IDs, for example GOA-00001.
                </p>
            </div>

            <form onSubmit={submit} className="max-w-xl space-y-5 rounded-lg border border-stone-200 bg-white p-6">
                <Field label="Name" error={errors.name}>
                    <input
                        type="text"
                        className={inputClass}
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        placeholder="e.g. Rabbits"
                    />
                </Field>

                <Field
                    label="Code prefix"
                    error={errors.code_prefix}
                    hint={
                        prefixLocked
                            ? 'Locked because animals are already registered under this type.'
                            : '2 to 6 letters. Leave blank on create to generate it from the name.'
                    }
                >
                    <input
                        type="text"
                        className={`${inputClass} font-mono uppercase`}
                        value={data.code_prefix}
                        disabled={prefixLocked}
                        maxLength={6}
                        onChange={(e) => setData('code_prefix', e.target.value.toUpperCase())}
                        placeholder="e.g. RAB"
                    />
                </Field>

                <Field label="Description" error={errors.description}>
                    <textarea
                        rows={3}
                        className={inputClass}
                        value={data.description}
                        onChange={(e) => setData('description', e.target.value)}
                    />
                </Field>

                <label className="flex items-center gap-2 text-sm text-stone-700">
                    <input
                        type="checkbox"
                        checked={data.is_active}
                        onChange={(e) => setData('is_active', e.target.checked)}
                    />
                    Active (available when registering new animals)
                </label>
                {errors.is_active && <p className="text-sm text-red-600">{errors.is_active}</p>}

                <div className="flex gap-3 pt-2">
                    <button type="submit" disabled={processing} className={primaryButtonClass}>
                        {isEdit ? 'Save changes' : 'Create animal type'}
                    </button>
                    <Link href="/animal-types" className={secondaryButtonClass}>Cancel</Link>
                </div>
            </form>
        </AppLayout>
    );
}
