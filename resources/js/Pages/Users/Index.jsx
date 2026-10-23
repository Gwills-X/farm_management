import { Link, router } from "@inertiajs/react";
import { useState } from "react";
import AppLayout from "../../Layouts/AppLayout";
import Pagination from "../../Components/Pagination";
import {
    inputClass,
    primaryButtonClass,
    secondaryButtonClass,
} from "../../Components/Field";

export default function Index({ users, filters, currentUserId }) {
    const [form, setForm] = useState({
        search: filters.search ?? "",
        role: filters.role ?? "",
        status: filters.status ?? "",
    });

    const submit = (event) => {
        event.preventDefault();
        router.get("/users", form, { preserveState: true, replace: true });
    };

    const clear = () => {
        setForm({ search: "", role: "", status: "" });
        router.get("/users");
    };

    const toggle = (user) => {
        const action = user.is_active ? "deactivate" : "activate";
        if (
            window.confirm(
                `${action[0].toUpperCase() + action.slice(1)} ${user.name}?`,
            )
        ) {
            router.patch(
                `/users/${user.id}/active`,
                {},
                { preserveScroll: true },
            );
        }
    };

    const hasFilters = Object.values(filters).some((value) => value);

    return (
        <AppLayout>
            <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 className="text-2xl font-semibold">Users</h1>
                    <p className="text-sm text-stone-500">
                        Manager, staff, and administrator accounts. Only admins
                        can see this page.
                    </p>
                </div>
                <Link href="/users/create" className={primaryButtonClass}>
                    Add user
                </Link>
            </div>

            <form
                onSubmit={submit}
                className="mb-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-4"
            >
                <input
                    className={`${inputClass} lg:col-span-2`}
                    placeholder="Search name or email"
                    value={form.search}
                    onChange={(e) =>
                        setForm({ ...form, search: e.target.value })
                    }
                />
                <select
                    className={inputClass}
                    value={form.role}
                    onChange={(e) => setForm({ ...form, role: e.target.value })}
                >
                    <option value="">All roles</option>
                    <option value="admin">Admin</option>
                    <option value="manager">Manager</option>
                    <option value="staff">Staff</option>
                </select>
                <select
                    className={inputClass}
                    value={form.status}
                    onChange={(e) =>
                        setForm({ ...form, status: e.target.value })
                    }
                >
                    <option value="">All statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <div className="flex gap-2 sm:col-span-2 lg:col-span-4">
                    <button type="submit" className={primaryButtonClass}>
                        Filter
                    </button>
                    {hasFilters && (
                        <button
                            type="button"
                            onClick={clear}
                            className={secondaryButtonClass}
                        >
                            Clear
                        </button>
                    )}
                </div>
            </form>

            <div className="overflow-x-auto rounded-lg border border-stone-200 bg-white">
                <table className="min-w-full divide-y divide-stone-200 text-sm">
                    <thead className="bg-stone-50 text-left text-stone-600">
                        <tr>
                            <th className="px-4 py-3 font-medium">Name</th>
                            <th className="px-4 py-3 font-medium">Email</th>
                            <th className="px-4 py-3 font-medium">Role</th>
                            <th className="px-4 py-3 font-medium">Status</th>
                            <th className="px-4 py-3 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-stone-100">
                        {users.data.map((user) => {
                            const isSelf = user.id === currentUserId;
                            return (
                                <tr key={user.id}>
                                    <td className="px-4 py-3 font-medium">
                                        {user.name}
                                        {isSelf && (
                                            <span className="ml-2 text-xs text-stone-500">
                                                (you)
                                            </span>
                                        )}
                                    </td>
                                    <td className="px-4 py-3">{user.email}</td>
                                    <td className="px-4 py-3 capitalize">
                                        {user.role}
                                    </td>
                                    <td className="px-4 py-3">
                                        {user.is_active ? (
                                            <span className="text-green-700">
                                                Active
                                            </span>
                                        ) : (
                                            <span className="text-stone-500">
                                                Inactive
                                            </span>
                                        )}
                                    </td>
                                    <td className="whitespace-nowrap px-4 py-3">
                                        <Link
                                            href={`/users/${user.id}/edit`}
                                            className="text-green-700 hover:underline"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            type="button"
                                            onClick={() => toggle(user)}
                                            disabled={isSelf}
                                            title={
                                                isSelf
                                                    ? "You cannot deactivate your own account"
                                                    : undefined
                                            }
                                            className="ml-3 text-amber-700 hover:underline disabled:cursor-not-allowed disabled:opacity-40"
                                        >
                                            {user.is_active
                                                ? "Deactivate"
                                                : "Activate"}
                                        </button>
                                    </td>
                                </tr>
                            );
                        })}
                        {users.data.length === 0 && (
                            <tr>
                                <td
                                    colSpan={5}
                                    className="px-4 py-10 text-center text-stone-500"
                                >
                                    No users match these filters.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
            <Pagination links={users.links} />
        </AppLayout>
    );
}
