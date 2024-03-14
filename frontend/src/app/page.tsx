import MaxWidthWrapper from '~/app/_components/ui/MaxWidthWrapper';
import Link from 'next/link';
import { ArrowRight } from 'lucide-react';
import { buttonVariants } from '~/app/_components/ui/button';
import eventsImage from '../../public/landing-page-hero-event.png';
import Image from 'next/image';

export default async function Home() {
  return (
    <>
      <MaxWidthWrapper className='mb-12 mt-10 flex flex-col sm:mt-40'>
        <div className='relative'>
          <Image
            alt='Events Image'
            src={eventsImage}
            quality={100}
            sizes='100vw'
            style={{
              height: '600px',
              width: '600px',
            }}
            className='pointer-events-none absolute right-0 hidden bg-contain bg-right md:block'
          />
          <div className='mb-4 flex max-w-fit items-center justify-center space-x-2 overflow-hidden rounded-full border border-gray-200 bg-white px-7 py-2 shadow-md backdrop-blur transition-all hover:border-gray-300 hover:bg-white/50'>
            <p className='text-sm font-semibold text-gray-700'>
              Entenbrot ist jetzt öffentlich!
            </p>
          </div>
          <h1 className='max-w-4xl text-4xl font-bold md:text-6xl'>
            Schülerzuweisung?{' '}
          </h1>
          <h1 className='max-w-4xl text-4xl font-bold md:text-6xl'>
            Ein <span className='text-blue-500'>Algorithmus</span>, der passt!
          </h1>
          <p className='mt-7 max-w-prose text-gray-700 sm:text-lg'>
            Entenbrot ermöglicht die einfache Zuordnung von Schülern zu
            Veranstaltungen. Dokumente hochladen und direkt starten.
          </p>

          <Link
            className={buttonVariants({
              size: 'lg',
              className: 'mt-5',
            })}
            href='/upload/rooms'
          >
            Los geht&#96;s <ArrowRight className='ml-2 h-5 w-5' />
          </Link>
        </div>
      </MaxWidthWrapper>
    </>
  );
}
