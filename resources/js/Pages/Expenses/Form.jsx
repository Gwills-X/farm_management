import { Link, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Field, { inputClass, primaryButtonClass, secondaryButtonClass } from '../../Components/Field';
import { toDateInput } from '../../lib/format';

export default function Form({ expense, categories }) {
    const isEdit = Boolean(expense);

    const { data, setData, post, put, processing, errors } = useForm({
        expense_category_id: expense?.expense_category_id ?? (categories[0]?.id ?? ''),
        expense_date: toDateInput(expense?.expense_date) || new Date().toISOString().slice(0, 10),
        amount: expense?.amount ?? '',
        description: expense?.description ?? '',
    });

    const submit = (event) => {
        event.preventDefault();
        if (isEdit) {
            put(`/expenses/${expense.id}`);
        } else {
            post('/expenses');
        }
    };

    return (
        <AppLayout>
            <div className="mb-6">
                <h1 className="text-2xl font-semibold">{isEdit ? 'Edit expense' : 'Record expense'}</h1>
                <p className="text-sm text-stone-500">Expenses are operating costs. Animal purchases are recorded under Purchases.</p>
            </div>

            <form onSubmit={submit} className="max-w-2xl space-y-5 rounded-lg border border-stone-200 bg-white p-6">
                <div className="grid gap-5 sm:grid-cols-2">
                    <Field label="Category" error={errors.expense_category_id}>
                        <select className={inputClass} value={data.expense_category_id}
                            onChange={(e) => setData('expense_category_id', e.target.value)}>
                            {categories.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
                        </select>
                    </Field>
                    <Field label="Date" error={errors.expense_date}>
                        <input type="date" className={inputClass} value={data.expense_date}
                            onChange={(e) => setData('expense_date', e.target.value)} />
                    </Field>
                    <Field label="Amount (₦)" error={errors.amount}>
                        <input type="number" step="0.01" min="0.01" className={inputClass} value={data.amount}
                            onChange={(e) => setData('amount', e.target.value)} />
                    </Field>
                </div>
                <Field label="Description" error={errors.description}>
                    <input className={inputClass} value={data.description} placeholder="e.g. Bag of maize bran for February"
                        onChange={(e) => setData('description', e.target.value)} />
                </Field>

                <div className="flex gap-3">
                    <button type="submit" disabled={processing} className={primaryButtonClass}>
                        {isEdit ? 'Save changes' : 'Save expense'}
                    </button>
                    <Link href="/expenses" className={secondaryButtonClass}>Cancel</Link>
                </div>
            </form>
        </AppLayout>
    );
}
