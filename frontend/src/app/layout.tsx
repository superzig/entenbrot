import '~/styles/globals.css';

import { Inter } from 'next/font/google';

import { TRPCReactProvider } from '~/trpc/react';
import { cn } from '~/lib/utils';
import Navbar from '~/app/_components/ui/Navbar';

const inter = Inter({
    subsets: ['latin'],
    variable: '--font-sans',
});

export const metadata = {
    title: {
        template: '%s | Entenbrot',
        default: 'Entenbrot',
    },
    description: 'The official Entenbrot software.',
    icons: [{ rel: 'icon', url: '/favicon.ico' }],
};

export default function RootLayout({
    children,
}: {
    children: React.ReactNode;
}) {
    return (
        <html lang='en' className='light'>
            <body
                className={cn(
                    'grainy min-h-screen font-sans antialiased',
                    inter.className
                )}
            >
                <TRPCReactProvider>
                    <Navbar />
                    {children}
                </TRPCReactProvider>
            </body>
        </html>
    );
}
