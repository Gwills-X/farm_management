import { Link, useForm } from "@inertiajs/react";
import AppLayout from "../../Layouts/AppLayout";
import Field, {
    inputClass,
    primaryButtonClass,
    secondaryButtonClass,
} from "../../Components/Field";

function ResetPasswordCard({ user }) {
    const { data, setData, put, processing, errors, reset } = useForm({
        password: "",
        password_confirmation: "",
    });

    const submit = (event) => {
        event.preventDefault();
        put(`/users/${user.id}/password`, { onSuccess: () => reset() });
    };

    return (
        <form
            onSubmit={submit}
            className="max-w-xl space-y-4 rounded-lg border border-stone-200 bg-white p-6"
        >
            <h2 className="font-semibold">Reset password</h2>
            <p className="text-sm text-stone-500">
                Sets a new password for {user.name}. Tell them the new password
                securely.
            </p>
            <Field label="New password" error={errors.password}>
                <input
                    type="password"
                    autoComplete="new-password"
                    className={inputClass}
                    value={data.password}
                    onChange={(e) => setData("password", e.target.value)}
                />
            </Field>
            <Field label="Confirm new password">
                <input
                    type="password"
                    autoComplete="new-password"
                    className={inputClass}
                    value={data.password_confirmation}
                    onChange={(e) =>
                        setData("password_confirmation", e.target.value)
                    }
                />
            </Field>
            <button
                type="submit"
                disabled={processing}
                className={primaryButtonClass}
            >
                Reset password
            </button>
        </form>
    );
}

export default function Form({ user }) {
    const isEdit = Boolean(user);

    const { data, setData, post, put, processing, errors } = useForm({
        name: user?.name ?? "",
        email: user?.email ?? "",
        role: user?.role ?? "staff",
        password: "",
        password_confirmation: "",
    });

    const submit = (event) => {
        event.preventDefault();
        if (isEdit) {
            put(`/users/${user.id}`);
        } else {
            post("/users");
        }
    };

    return (
        <AppLayout>
            <div className="mb-6">
                <h1 className="text-2xl font-semibold">
                    {isEdit ? `Edit ${user.name}` : "Add user"}
                </h1>
                <p className="text-sm text-stone-500">
                    Managers and staff can record farm operations. Admins can
                    also manage users and animal types.
                </p>
            </div>

            <form
                onSubmit={submit}
                className="max-w-xl space-y-5 rounded-lg border border-stone-200 bg-white p-6"
            >
                <Field label="Full name" error={errors.name}>
                    <input
                        className={inputClass}
                        value={data.name}
                        onChange={(e) => setData("name", e.target.value)}
                    />
                </Field>
                <Field label="Email" error={errors.email}>
                    <input
                        type="email"
                        className={inputClass}
                        value={data.email}
                        onChange={(e) => setData("email", e.target.value)}
                    />
                </Field>
                <Field label="Role" error={errors.role || errors.role_change}>
                    <select
                        className={inputClass}
                        value={data.role}
                        onChange={(e) => setData("role", e.target.value)}
                    >
                        <option value="staff">Staff</option>
                        <option value="manager">Manager</option>
                        <option value="admin">Admin</option>
                    </select>
                </Field>

                {!isEdit && (
                    <>
                        <Field
                            label="Password"
                            error={errors.password}
                            hint="At least 8 characters, with letters and numbers."
                        >
                            <input
                                type="password"
                                autoComplete="new-password"
                                className={inputClass}
                                value={data.password}
                                onChange={(e) =>
                                    setData("password", e.target.value)
                                }
                            />
                        </Field>
                        <Field label="Confirm password">
                            <input
                                type="password"
                                autoComplete="new-password"
                                className={inputClass}
                                value={data.password_confirmation}
                                onChange={(e) =>
                                    setData(
                                        "password_confirmation",
                                        e.target.value,
                                    )
                                }
                            />
                        </Field>
                    </>
                )}

                <div className="flex gap-3">
                    <button
                        type="submit"
                        disabled={processing}
                        className={primaryButtonClass}
                    >
                        {isEdit ? "Save changes" : "Create user"}
                    </button>
                    <Link href="/users" className={secondaryButtonClass}>
                        Cancel
                    </Link>
                </div>
            </form>

            {isEdit && (
                <div className="mt-8">
                    <ResetPasswordCard user={user} />
                </div>
            )}
        </AppLayout>
    );
}
