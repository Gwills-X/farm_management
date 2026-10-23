import { STATUS_LABELS } from '../lib/format';

const STYLES = {
    active: 'bg-green-100 text-green-800',
    sold: 'bg-blue-100 text-blue-800',
    deceased: 'bg-stone-200 text-stone-700',
    archived: 'bg-amber-100 text-amber-800',
};

export default function StatusBadge({ status }) {
    return (
        <span className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ${STYLES[status] ?? 'bg-stone-100 text-stone-700'}`}>
            {STATUS_LABELS[status] ?? status}
        </span>
    );
}
