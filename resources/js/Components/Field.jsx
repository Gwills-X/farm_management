export const inputClass =
    'w-full rounded-md border border-stone-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-green-600 focus:outline-none focus:ring-1 focus:ring-green-600 disabled:bg-stone-100 disabled:text-stone-500';

export const primaryButtonClass =
    'inline-flex items-center rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800 disabled:opacity-50';

export const secondaryButtonClass =
    'inline-flex items-center rounded-md border border-stone-300 bg-white px-4 py-2 text-sm font-medium text-stone-700 hover:bg-stone-50';

export default function Field({ label, error, hint, children }) {
    return (
        <div>
            <label className="mb-1 block text-sm font-medium text-stone-700">{label}</label>
            {children}
            {hint && <p className="mt-1 text-xs text-stone-500">{hint}</p>}
            {error && <p className="mt-1 text-sm text-red-600">{error}</p>}
        </div>
    );
}
