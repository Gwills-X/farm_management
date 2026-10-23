import { Link, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Field, { inputClass, primaryButtonClass, secondaryButtonClass } from '../../Components/Field';
import { formatNaira } from '../../lib/format';

const blankItem = (animalTypes) => ({
    animal_type_id: animalTypes[0]?.id ?? '',
    name: '',
    sex: 'unknown',
    breed: '',
    date_of_birth: '',
    weight_kg: '',
    unit_cost: '',
});

export default function Create({ animalTypes }) {
    const { data, setData, post, processing, errors } = useForm({
        purchase_date: new Date().toISOString().slice(0, 10),
        seller_name: '',
        notes: '',
        items: [blankItem(animalTypes)],
    });

    const updateItem = (index, field, value) => {
        const items = data.items.map((item, i) => (i === index ? { ...item, [field]: value } : item));
        setData('items', items);
    };

    const addItem = () => setData('items', [...data.items, blankItem(animalTypes)]);

    const removeItem = (index) => {
        if (data.items.length === 1) return;
        setData('items', data.items.filter((_, i) => i !== index));
    };

    const total = data.items.reduce((sum, item) => sum + (Number(item.unit_cost) || 0), 0);

    const submit = (event) => {
        event.preventDefault();
        post('/purchases');
    };

    if (animalTypes.length === 0) {
        return (
            <AppLayout>
                <h1 className="mb-4 text-2xl font-semibold">Record purchase</h1>
                <div className="max-w-xl rounded-lg border border-amber-200 bg-amber-50 p-6">
                    <p className="text-amber-900">Create an active animal type before recording a purchase.</p>
                    <Link href="/animal-types/create" className={`${primaryButtonClass} mt-4`}>Create an animal type</Link>
                </div>
            </AppLayout>
        );
    }

    return (
        <AppLayout>
            <div className="mb-6">
                <h1 className="text-2xl font-semibold">Record purchase</h1>
                <p className="text-sm text-stone-500">
                    Each animal you add is registered in the farm register with an automatic code and linked to this purchase.
                </p>
            </div>

            <form onSubmit={submit} className="space-y-6">
                <div className="grid gap-5 rounded-lg border border-stone-200 bg-white p-6 sm:grid-cols-3">
                    <Field label="Purchase date" error={errors.purchase_date}>
                        <input type="date" className={inputClass} value={data.purchase_date}
                            onChange={(e) => setData('purchase_date', e.target.value)} />
                    </Field>
                    <Field label="Seller (optional)" error={errors.seller_name}>
                        <input className={inputClass} value={data.seller_name}
                            onChange={(e) => setData('seller_name', e.target.value)} />
                    </Field>
                    <Field label="Notes (optional)" error={errors.notes}>
                        <input className={inputClass} value={data.notes}
                            onChange={(e) => setData('notes', e.target.value)} />
                    </Field>
                </div>

                {errors.items && <p className="text-sm text-red-600">{errors.items}</p>}

                <div className="space-y-4">
                    {data.items.map((item, index) => (
                        <div key={index} className="rounded-lg border border-stone-200 bg-white p-5">
                            <div className="mb-4 flex items-center justify-between">
                                <h2 className="font-semibold">Animal {index + 1}</h2>
                                {data.items.length > 1 && (
                                    <button type="button" onClick={() => removeItem(index)} className="text-sm text-red-700 hover:underline">
                                        Remove
                                    </button>
                                )}
                            </div>
                            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                <Field label="Type" error={errors[`items.${index}.animal_type_id`]}>
                                    <select className={inputClass} value={item.animal_type_id}
                                        onChange={(e) => updateItem(index, 'animal_type_id', e.target.value)}>
                                        {animalTypes.map((t) => <option key={t.id} value={t.id}>{t.name}</option>)}
                                    </select>
                                </Field>
                                <Field label="Name (optional)" error={errors[`items.${index}.name`]}>
                                    <input className={inputClass} value={item.name}
                                        onChange={(e) => updateItem(index, 'name', e.target.value)} />
                                </Field>
                                <Field label="Sex" error={errors[`items.${index}.sex`]}>
                                    <select className={inputClass} value={item.sex}
                                        onChange={(e) => updateItem(index, 'sex', e.target.value)}>
                                        <option value="unknown">Unknown</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                    </select>
                                </Field>
                                <Field label="Breed (optional)" error={errors[`items.${index}.breed`]}>
                                    <input className={inputClass} value={item.breed}
                                        onChange={(e) => updateItem(index, 'breed', e.target.value)} />
                                </Field>
                                <Field label="Date of birth (optional)" error={errors[`items.${index}.date_of_birth`]}>
                                    <input type="date" className={inputClass} value={item.date_of_birth}
                                        onChange={(e) => updateItem(index, 'date_of_birth', e.target.value)} />
                                </Field>
                                <Field label="Weight (kg, optional)" error={errors[`items.${index}.weight_kg`]}>
                                    <input type="number" step="0.01" min="0" className={inputClass} value={item.weight_kg}
                                        onChange={(e) => updateItem(index, 'weight_kg', e.target.value)} />
                                </Field>
                                <Field label="Unit cost (₦)" error={errors[`items.${index}.unit_cost`]}>
                                    <input type="number" step="0.01" min="0" className={inputClass} value={item.unit_cost}
                                        onChange={(e) => updateItem(index, 'unit_cost', e.target.value)} />
                                </Field>
                            </div>
                        </div>
                    ))}
                </div>

                <div className="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-stone-200 bg-white p-5">
                    <button type="button" onClick={addItem} className={secondaryButtonClass}>Add another animal</button>
                    <div className="text-right">
                        <div className="text-sm text-stone-500">Total cost</div>
                        <div className="text-xl font-semibold">{formatNaira(total)}</div>
                    </div>
                </div>

                <div className="flex gap-3">
                    <button type="submit" disabled={processing} className={primaryButtonClass}>Save purchase</button>
                    <Link href="/purchases" className={secondaryButtonClass}>Cancel</Link>
                </div>
            </form>
        </AppLayout>
    );
}
