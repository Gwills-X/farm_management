import { Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import { inputClass, primaryButtonClass, secondaryButtonClass } from '../../Components/Field';
import { formatDate, formatNaira } from '../../lib/format';

const FILTER_FIELDS = {
    animal_type_id: { label: 'Animal type', kind: 'animalType' },
    expense_category_id: { label: 'Expense category', kind: 'category' },
    status: {
        label: 'Animal status',
        kind: 'select',
        options: [['active', 'Active'], ['sold', 'Sold'], ['deceased', 'Deceased'], ['archived', 'Archived']],
    },
    health_status: {
        label: 'Health status',
        kind: 'select',
        options: [['healthy', 'Healthy'], ['monitoring', 'Monitoring'], ['sick', 'Sick'], ['recovered', 'Recovered']],
    },
    acquisition_type: {
        label: 'Acquisition',
        kind: 'select',
        options: [['purchased', 'Purchased'], ['born_on_farm', 'Born on farm'], ['other', 'Other']],
    },
    payment_status: {
        label: 'Payment status',
        kind: 'select',
        options: [['unpaid', 'Unpaid'], ['partial', 'Part paid'], ['paid', 'Paid']],
    },
    from: { label: 'From', kind: 'date' },
    to: { label: 'To', kind: 'date' },
};

function renderCell(column, value) {
    if (value === null || value === undefined || value === '') return '—';
    if (column.type === 'date') return formatDate(value);
    if (column.type === 'money') return formatNaira(value);
    return value;
}

export default function Index({ type, definitions, filters, report, animalTypes, categories }) {
    const [form, setForm] = useState(filters);
    const activeDefinition = definitions.find((d) => d.key === type);

    const apply = (event) => {
        event.preventDefault();
        router.get('/reports', { type, ...form }, { preserveState: true, replace: true });
    };

    const clear = () => {
        const cleared = Object.fromEntries(Object.keys(form).map((k) => [k, '']));
        setForm(cleared);
        router.get('/reports', { type }, { replace: true });
    };

    const exportParams = new URLSearchParams({ type, ...Object.fromEntries(Object.entries(filters).filter(([, v]) => v !== '')), export: 'csv' });

    const renderFilter = (key) => {
        const field = FILTER_FIELDS[key];
        if (!field) return null;

        const value = form[key] ?? '';
        const onChange = (e) => setForm({ ...form, [key]: e.target.value });

        let input;
        if (field.kind === 'date') {
            input = <input type="date" className={inputClass} value={value} onChange={onChange} />;
        } else if (field.kind === 'animalType') {
            input = (
                <select className={inputClass} value={value} onChange={onChange}>
                    <option value="">All types</option>
                    {animalTypes.map((t) => <option key={t.id} value={t.id}>{t.name}</option>)}
                </select>
            );
        } else if (field.kind === 'category') {
            input = (
                <select className={inputClass} value={value} onChange={onChange}>
                    <option value="">All categories</option>
                    {categories.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
                </select>
            );
        } else {
            input = (
                <select className={inputClass} value={value} onChange={onChange}>
                    <option value="">All</option>
                    {field.options.map(([v, l]) => <option key={v} value={v}>{l}</option>)}
                </select>
            );
        }

        return (
            <div key={key}>
                <label className="mb-1 block text-xs text-stone-500">{field.label}</label>
                {input}
            </div>
        );
    };

    return (
        <AppLayout>
            <div className="mb-6 print:mb-2">
                <h1 className="text-2xl font-semibold">Reports</h1>
                <p className="text-sm text-stone-500 print:hidden">Choose a report, set filters, then print or export to CSV.</p>
            </div>

            <div className="mb-6 flex flex-wrap gap-2 print:hidden">
                {definitions.map((d) => (
                    <Link
                        key={d.key}
                        href={`/reports?type=${d.key}`}
                        preserveState={false}
                        className={`rounded-md px-3 py-2 text-sm ${d.key === type ? 'bg-green-700 text-white' : 'border border-stone-300 bg-white hover:bg-stone-50'}`}
                    >
                        {d.label}
                    </Link>
                ))}
            </div>

            <form onSubmit={apply} className="mb-6 grid gap-3 rounded-lg border border-stone-200 bg-white p-4 print:hidden sm:grid-cols-2 lg:grid-cols-4">
                {activeDefinition.filters.map((key) => renderFilter(key))}
                {activeDefinition.filters.length === 0 && (
                    <p className="text-sm text-stone-500 sm:col-span-2 lg:col-span-4">This report has no filters.</p>
                )}
                <div className="flex flex-wrap items-end gap-2 sm:col-span-2 lg:col-span-4">
                    <button type="submit" className={primaryButtonClass}>Apply filters</button>
                    <button type="button" onClick={clear} className={secondaryButtonClass}>Clear</button>
                    <button type="button" onClick={() => window.print()} className={secondaryButtonClass}>Print</button>
                    <a href={`/reports?${exportParams.toString()}`} className={secondaryButtonClass}>Export CSV</a>
                </div>
            </form>

            <h2 className="mb-3 text-lg font-semibold">{report.title}</h2>

            <div className="mb-4 grid gap-4 sm:grid-cols-3">
                {report.summary.map((s) => (
                    <div key={s.label} className="rounded-lg border border-stone-200 bg-white p-4">
                        <div className="text-xs uppercase tracking-wide text-stone-500">{s.label}</div>
                        <div className="mt-1 text-lg font-semibold">{s.type === 'money' ? formatNaira(s.value) : s.value}</div>
                    </div>
                ))}
            </div>

            <div className="overflow-x-auto rounded-lg border border-stone-200 bg-white">
                <table className="min-w-full divide-y divide-stone-200 text-sm">
                    <thead className="bg-stone-50 text-left text-stone-600">
                        <tr>
                            {report.columns.map((c) => (
                                <th key={c.key} className={`whitespace-nowrap px-3 py-2 font-medium ${c.type === 'money' || c.type === 'number' ? 'text-right' : ''}`}>
                                    {c.label}
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-stone-100">
                        {report.rows.map((row, i) => (
                            <tr key={i}>
                                {report.columns.map((c) => (
                                    <td key={c.key} className={`px-3 py-2 ${c.type === 'money' || c.type === 'number' ? 'text-right' : ''}`}>
                                        {renderCell(c, row[c.key])}
                                    </td>
                                ))}
                            </tr>
                        ))}
                        {report.rows.length === 0 && (
                            <tr>
                                <td colSpan={report.columns.length} className="px-4 py-10 text-center text-stone-500">
                                    No records match these filters.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </AppLayout>
    );
}
