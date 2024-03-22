import Link from 'next/link';
import MaxWidthWrapper from '~/app/_components/ui/MaxWidthWrapper';
import { buttonVariants } from '~/app/_components/ui/button';
import { ArrowRight } from 'lucide-react';
import Image from 'next/image';
import logoImage from '../../public/logo.png';

export default function NotFound() {
    return (
        <MaxWidthWrapper className='flex flex-col justify-center pt-36 text-center align-middle'>
            <div>
                <Image
                    alt='Events Image'
                    src={logoImage}
                    quality={100}
                    style={{
                        width: '300px',
                    }}
                    priority={true}
                    className='pointer-events-none mx-auto hidden bg-contain md:block'
                />
                <h1 className='text-2xl font-semibold'>
                    Entschuldigung, diese Seite ist weggeflogen.
                </h1>
                <p>- genau wie unsere Ente mit dem Brot!</p>
            </div>
            <div className='mt-3'>
                <Link
                    className={buttonVariants({
                        size: 'lg',
                        className: 'mt-5',
                    })}
                    href='/'
                >
                    Zurück zur Startseite{' '}
                    <ArrowRight className='ml-2 h-5 w-5' />
                </Link>
            </div>
        </MaxWidthWrapper>
    );
}
