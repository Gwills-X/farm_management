export const SEX_LABELS = { male: 'Male', female: 'Female', unknown: 'Unknown' };

export const ACQUISITION_LABELS = {
    purchased: 'Purchased',
    born_on_farm: 'Born on farm',
    other: 'Other',
};

export const STATUS_LABELS = {
    active: 'Active',
    sold: 'Sold',
    deceased: 'Deceased',
    archived: 'Archived',
};

export const HEALTH_LABELS = {
    healthy: 'Healthy',
    monitoring: 'Monitoring',
    sick: 'Sick',
    recovered: 'Recovered',
};

export const PAYMENT_LABELS = {
    unpaid: 'Unpaid',
    partial: 'Part paid',
    paid: 'Paid',
};

export const PAYMENT_METHOD_LABELS = {
    cash: 'Cash',
    bank_transfer: 'Bank transfer',
    pos: 'POS',
    other: 'Other',
};

/** Formats a Laravel date value (e.g. "2025-01-01T00:00:00.000000Z") without day shifting. */
export function formatDate(value) {
    if (!value) return '—';

    const [year, month, day] = String(value).slice(0, 10).split('-').map(Number);
    if (!year || !month || !day) return '—';

    return new Date(year, month - 1, day).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
}

/** Formats a Laravel datetime value in the viewer's local time. */
export function formatDateTime(value) {
    if (!value) return '—';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '—';

    return date.toLocaleString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

/** Returns YYYY-MM-DD for <input type="date">. */
export function toDateInput(value) {
    return value ? String(value).slice(0, 10) : '';
}

/** Returns YYYY-MM-DDTHH:mm in local time for <input type="datetime-local">. */
export function toDateTimeInput(value) {
    if (!value) return '';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '';

    const pad = (n) => String(n).padStart(2, '0');
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

export function formatWeight(value) {
    if (value === null || value === undefined || value === '') return '—';
    return `${Number(value).toFixed(2)} kg`;
}

/** Money display in Nigerian naira. Accepts decimal strings or numbers from the server. */
export function formatNaira(value) {
    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        minimumFractionDigits: 2,
    }).format(Number(value ?? 0));
}
