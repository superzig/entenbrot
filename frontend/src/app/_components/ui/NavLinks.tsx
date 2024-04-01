'use client';
import Link from 'next/link';
import {usePathname} from 'next/navigation';
import {cn} from "~/lib/utils";
import {buttonVariants} from "~/app/_components/ui/button";

// Map of links to display in the side navigation.
// Depending on the size of the application, this would be stored in a database.
const links = [
    {
        id: "upload",
        name: 'Auswertung starten',
        href: '/upload/rooms',
        pattern: new RegExp('upload\/*'),
    },
    {
        id: "list",
        name: 'Ergebnisse',
        href: '/list'
    },
];

export default function NavLinks() {
    const pathname = usePathname();

    return (
        <>
            {links.map((link) => {
                return (
                    <Link
                        key={link.name}
                        href={link.href}
                        className={cn(buttonVariants({
                            variant: 'link',
                        }), {
                            'text-primary': (link?.pattern) ? link.pattern.test(pathname) : pathname === link.href,
                            "hidden md:block": link.id !== 'list',
                        })}
                    >
                        {link.name}
                    </Link>
                );
            })}
        </>
    );
}