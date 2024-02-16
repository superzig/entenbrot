import "~/styles/globals.css";

import {Inter} from "next/font/google";

import {TRPCReactProvider} from "~/trpc/react";
import {cn} from "~/lib/utils"
import Navbar from "~/app/_components/ui/Navbar";

const inter = Inter({
    subsets: ["latin"],
    variable: "--font-sans",
});

export const metadata = {
    title: {
        template: "%s | Pathway",
        default: "Pathway"
    },
    description: "The official Pathway software.",
    icons: [{rel: "icon", url: "/favicon.ico"}],
};

export default function RootLayout({
                                       children,
                                   }: {
    children: React.ReactNode;
}) {
    return (
        <html lang="en" className="light">
        <body className={cn(
            'min-h-screen font-sans antialiased grainy',
            inter.className
        )}>
        <TRPCReactProvider>
            <Navbar/>
            {children}
        </TRPCReactProvider>
        </body>
        </html>
    );
}
