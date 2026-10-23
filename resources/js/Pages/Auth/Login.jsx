import { useForm } from '@inertiajs/react';
import Field, { inputClass, primaryButtonClass } from '../../Components/Field';

export default function Login() {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (event) => {
        event.preventDefault();
        post('/login', {
            onFinish: () => reset('password'),
        });
    };

    return (
        <div className="flex min-h-screen items-center justify-center bg-green-50 p-4">
            <form onSubmit={submit} className="w-full max-w-sm space-y-4 rounded-xl bg-white p-6 shadow">
                <h1 className="text-xl font-semibold text-stone-800">Farm Management System</h1>
                <p className="text-sm text-stone-500">Sign in to continue.</p>

                <Field label="Email" error={errors.email}>
                    <input
                        type="email"
                        className={inputClass}
                        value={data.email}
                        autoComplete="username"
                        onChange={(e) => setData('email', e.target.value)}
                    />
                </Field>

                <Field label="Password" error={errors.password}>
                    <input
                        type="password"
                        className={inputClass}
                        value={data.password}
                        autoComplete="current-password"
                        onChange={(e) => setData('password', e.target.value)}
                    />
                </Field>

                <label className="flex items-center gap-2 text-sm text-stone-600">
                    <input
                        type="checkbox"
                        checked={data.remember}
                        onChange={(e) => setData('remember', e.target.checked)}
                    />
                    Remember me
                </label>

                <button type="submit" disabled={processing} className={`${primaryButtonClass} w-full justify-center`}>
                    Sign in
                </button>
            </form>
        </div>
    );
}
