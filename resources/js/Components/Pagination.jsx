import { Link } from '@inertiajs/react';

export default function Pagination({ links }) {
    if (!links || links.length <= 3) return null;

    return (
        <nav className="mt-4 flex flex-wrap items-center gap-1">
            {links.map((link, index) => {
                const classes = `rounded-md px-3 py-1.5 text-sm ${
                    link.active
                        ? 'bg-green-700 text-white'
                        : link.url
                          ? 'bg-white text-stone-700 hover:bg-stone-100 border border-stone-200'
                          : 'text-stone-400'
                }`;

                return link.url ? (
                    <Link key={index} href={link.url} preserveScroll className={classes}>
                        <span dangerouslySetInnerHTML={{ __html: link.label }} />
                    </Link>
                ) : (
                    <span key={index} className={classes} dangerouslySetInnerHTML={{ __html: link.label }} />
                );
            })}
        </nav>
    );
}
