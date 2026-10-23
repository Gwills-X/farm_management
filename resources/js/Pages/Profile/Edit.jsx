import { useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Field, { inputClass, primaryButtonClass } from '../../Components/Field';

export default function Edit() {
    const { data, setData, put, processing, errors, reset } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (event) => {
        event.preventDefault();
        put('/profile/password', {
            onSuccess: () => reset(),
        });
    };

    return (
        <AppLayout>
            <div className="mb-6">
                <h1 className="text-2xl font-semibold">Change my password</h1>
                <p className="text-sm text-stone-500">You need your current password to set a new one.</p>
            </div>

            <form onSubmit={submit} className="max-w-md space-y-5 rounded-lg border border-stone-200 bg-white p-6">
                <Field label="Current password" error={errors.current_password}>
                    <input type="password" autoComplete="current-password" className={inputClass} value={data.current_password}
                        onChange={(e) => setData('current_password', e.target.value)} />
                </Field>
                <Field label="New password" error={errors.password} hint="At least 8 characters, with letters and numbers.">
                    <input type="password" autoComplete="new-password" className={inputClass} value={data.password}
                        onChange={(e) => setData('password', e.target.value)} />
                </Field>
                <Field label="Confirm new password">
                    <input type="password" autoComplete="new-password" className={inputClass} value={data.password_confirmation}
                        onChange={(e) => setData('password_confirmation', e.target.value)} />
                </Field>
                <button type="submit" disabled={processing} className={primaryButtonClass}>Change password</button>
            </form>
        </AppLayout>
    );
}
