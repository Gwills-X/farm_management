import { Link, usePage } from "@inertiajs/react";
import FlashMessages from "../Components/FlashMessages";

const NAV_ITEMS = [
    { label: "Dashboard", href: "/" },
    { label: "Animals", href: "/animals" },
    { label: "Animal Types", href: "/animal-types" },
    { label: "Health", href: "/health-records" },
    { label: "Feeding", href: "/feeding-records" },
    { label: "Purchases", href: "/purchases" },
    { label: "Sales", href: "/sales" },
    { label: "Expenses", href: "/expenses" },
    { label: "Reports", href: "/reports" },
];

const ADMIN_NAV_ITEMS = [{ label: "Users", href: "/users" }];

function isActive(currentUrl, href) {
    const path = currentUrl.split("?")[0];
    return href === "/" ? path === "/" : path.startsWith(href);
}

export default function AppLayout({ children }) {
    const page = usePage();
    const user = page.props.auth?.user;
    const navItems =
        user?.role === "admin" ? [...NAV_ITEMS, ...ADMIN_NAV_ITEMS] : NAV_ITEMS;

    return (
        <div className="min-h-screen bg-stone-50 text-stone-800 md:flex">
            <aside className="flex flex-col bg-green-950 p-4 text-green-50 print:hidden md:sticky md:top-0 md:h-screen md:w-64 md:shrink-0">
                <div className="mb-4 border-b border-green-800/70 pb-4 md:mb-6">
                    <div className="text-lg font-bold tracking-wide">
                        Administrator
                    </div>
                    <div className="mt-1 text-xs text-green-300">
                        Operations console
                    </div>
                </div>
                <nav className="sidebar-scroll flex gap-2 overflow-x-auto md:min-h-0 md:flex-1 md:flex-col md:overflow-x-hidden md:overflow-y-auto">
                    {navItems.map((item) => (
                        <Link
                            key={item.href}
                            href={item.href}
                            className={`whitespace-nowrap rounded-md border-l-2 px-3 py-2.5 text-sm transition-colors ${isActive(page.url, item.href) ? "border-emerald-300 bg-green-800 text-white" : "border-transparent text-green-100 hover:bg-green-900 hover:text-white"}`}
                        >
                            {item.label}
                        </Link>
                    ))}
                </nav>
            </aside>

            <div className="min-w-0 flex-1">
                <header className="flex flex-wrap items-center justify-between gap-3 border-b border-stone-200 bg-white px-4 py-3 print:hidden md:px-6">
                    <span className="text-sm font-medium text-stone-600">
                        {user?.name}
                        {user?.role === "admin" ? (
                            <span className="ml-2 rounded bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">
                                Administrator
                            </span>
                        ) : null}
                    </span>
                    <div className="flex items-center gap-2">
                        <Link
                            href="/profile"
                            className="rounded-md border border-stone-200 px-3 py-2 text-sm font-medium text-stone-700 transition hover:border-green-700 hover:text-green-800"
                        >
                            My profile
                        </Link>
                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            className="inline-flex items-center gap-2 rounded-md bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2"
                        >
                            <svg
                                aria-hidden="true"
                                viewBox="0 0 20 20"
                                fill="none"
                                className="h-4 w-4"
                            >
                                <path
                                    d="M8 3H4.75A1.75 1.75 0 0 0 3 4.75v10.5C3 16.216 3.784 17 4.75 17H8m4-3 4-4-4-4m4 4H7"
                                    stroke="currentColor"
                                    strokeWidth="1.7"
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                />
                            </svg>
                            Log out
                        </Link>
                    </div>
                </header>
                <main className="p-4 md:p-6">
                    <FlashMessages />
                    {children}
                </main>
            </div>
        </div>
    );
}
