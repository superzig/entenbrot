"use client";
import MaxWidthWrapper from '~/app/_components/ui/MaxWidthWrapper';
import Link from 'next/link';
import { ArrowRight } from 'lucide-react';
import { buttonVariants } from '~/app/_components/ui/button';
import Image from 'next/image';
import logoImage from '../../public/logo.png';
import scorePreviewImage from '../../public/success-score-preview.png';
import cachedResultsPreviewImage from '../../public/cached-results-preview.png';
import {toast} from "~/app/_components/ui/use-toast";
import {useEffect} from "react";

const checkOldCache = () => {
    try {
        void fetch('http://localhost:8000/api/data/checkCache', {
            method: 'GET',
        }).then((response) => {
            if (response.ok) {
                return response.json();
            }
            throw new Error('Ein Fehler ist aufgetreten');
        })
            .then((data) => {
                if (data.success) {
                    toast({
                        title: 'Vorherige Auswertungen',
                        description: data.message || 'Der Cache wurde erfolgreich gelöscht',
                    });
                }
            }).catch((error) => {
                toast({
                    title: 'Fehler bei der Cache-Überprüfung',
                    description: error?.message || 'Ein Fehler ist aufgetreten',
                    variant: 'destructive'
                })
            });
    } catch (error) {
        toast({
            title: 'Fehler bei der Cache-Überprüfung',
            description: error?.message || 'Ein Fehler ist aufgetreten',
            ariant: 'destructive'
        })
    }
}

export default function Home() {

    useEffect(() => {
        checkOldCache();
    }, []);

    return (
        <>
            <MaxWidthWrapper className='mb-12 mt-10 flex flex-col sm:mt-20'>
                <div className='flex flex-col justify-start gap-x-36 md:flex-row-reverse'>
                    <div>
                        <Image
                            alt='Events Image'
                            src={logoImage}
                            quality={100}
                            style={{
                                width: '500px',
                            }}
                            priority={true}
                            className='pointer-events-none hidden bg-contain md:block'
                        />
                    </div>
                    <div>
                        <div
                            className='mb-4 flex max-w-fit items-center justify-center space-x-2 overflow-hidden rounded-full border border-gray-200 bg-white px-7 py-2 shadow-md backdrop-blur transition-all hover:border-gray-300 hover:bg-white/50'>
                            <p className='text-sm font-semibold text-gray-700'>
                                Entenbrot ist jetzt öffentlich!
                            </p>
                        </div>
                        <h1 className='text-4xl font-bold md:text-6xl'>
                            Schülerzuweisung?{' '}
                        </h1>
                        <h1 className='text-4xl font-bold md:text-6xl'>
                            Ein{' '}
                            <span className='text-blue-500'>Algorithmus</span>,
                            der passt!
                        </h1>
                        <p className='mt-7 text-gray-700 sm:text-lg'>
                            Entenbrot ermöglicht die einfache Zuordnung von
                            Schülern zu Veranstaltungen. Dokumente hochladen und
                            direkt starten.
                        </p>

                        <Link
                            className={buttonVariants({
                                size: 'lg',
                                className: 'mt-5',
                            })}
                            href='/upload/rooms'
                        >
                            Los geht&#96;s{' '}
                            <ArrowRight className='ml-2 h-5 w-5'/>
                        </Link>
                    </div>
                </div>

                {/* value proposition section */}

                <div>
                    <div className="relative isolate">

                        <div aria-hidden="true"
                             className="pointer-events-none absolute inset-x-0 -top-50 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80">
                            <div style={{
                                clipPath: "polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"
                            }}
                                 className="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[#3b82f6] to-[#ffbe3a] opacity-30 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]"></div>
                        </div>

                        <div>
                            <div className="mx-auto max-w-6xl px-6 lg:px-8">
                                <div className="mt-16 flow-root sm:mt-24">
                                    <div
                                        className="-m-2 rounded-xl bg-gray-900/5 p-2 ring-1 ring-inset ring-gray-900/10 lg:-m-4 lg:rounded-2xl lg:p-4">
                                        <Image src={scorePreviewImage} alt="product preview" width={1364} height={866}
                                               quality={100}
                                               className="rounded-md bg-white shadow-2xl ring-1 ring-gray-900/10"/>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div aria-hidden="true"
                             className="pointer-events-none absolute inset-x-0 -top-50 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80">
                            <div style={{
                                clipPath: "polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"
                            }}
                                 className="relative left-[calc(50%-13rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[#3b82f6] to-[#ffbe3a] opacity-30 sm:left-[calc(50%-36rem)] sm:w-[72.1875rem]"></div>
                        </div>
                    </div>
                </div>

                {/* Feature section */}
                <div className="mx-auto mb-32 mt-32 max-w-5xl sm:mt-56">
                    <div className="mb-12 px-6 lg:px-8">
                        <div className="mx-auto mx-w-2xl sm:text-center">
                            <h2 className="mt-2 font-bold text-4xl text-gray-900 sm:text-5xl">
                                Deine Auswertung in Minuten
                            </h2>
                            <p className="mt-4 text-lg text-gray-600">
                                Schüler zu Veranstaltungen zuordnen war noch nie so einfach wie mit Entenbrot.
                            </p>
                        </div>
                    </div>

                    {/* steps */}
                    <ol className="my-8 space-y-4 pt-8 md:flex md:space-x-12 md:space-y-0">
                        <li className="md:flex-1">
                            <div
                                className="flex flex-col space-y-2 border-l-4 border-zinc-300 py-2 pl-4 md:border-l-0 md:border-t-2 md:pb-0 md:pl-0 md:pt-4">
                                <span className="text-sm font-medium text-blue-600">Schritt 1</span>
                                <span className="text-xl font-semibold">Deine Excel Dateien hochladen</span>
                                <span className="mt-2 text-zinc-700">
                                    Wir verarbeiten deine Dateien und machen sie für dich bereit.
                           </span>
                            </div>
                        </li>
                        <li className="md:flex-1">
                            <div
                                className="flex flex-col space-y-2 border-l-4 border-zinc-300 py-2 pl-4 md:border-l-0 md:border-t-2 md:pb-0 md:pl-0 md:pt-4">
                                <span className="text-sm font-medium text-blue-600">Schritt 2</span>
                                <span className="text-xl font-semibold">Auswertung starten</span>
                                <span className="mt-2 text-zinc-700">
                                    Unsere Ente erstellt blitzschnell eine Auswertung für Sie.
                           </span>
                            </div>
                        </li>
                        <li className="md:flex-1">
                            <div
                                className="flex flex-col space-y-2 border-l-4 border-zinc-300 py-2 pl-4 md:border-l-0 md:border-t-2 md:pb-0 md:pl-0 md:pt-4">
                                <span className="text-sm font-medium text-blue-600">Schritt 3</span>
                                <span className="text-xl font-semibold">Dokumente herunterladen</span>
                                <span className="mt-2 text-zinc-700">
                                    Laufzettel, Anwesenheitsliste und Veranstaltungen lassen sich bequem herunterladen.
                                    So einfach war es noch nie.
                           </span>
                            </div>
                        </li>
                    </ol>

                    <div className="mx-auto max-w-6xl px-6 lg:px-8">
                        <div className="mt-16 flow-root sm:mt-24">
                            <div
                                className="-m-2 rounded-xl bg-gray-900/5 p-2 ring-1 ring-inset ring-gray-900/10 lg:-m-4 lg:rounded-2xl lg:p-4">
                                <Image
                                    src={cachedResultsPreviewImage}
                                    alt="uploading file preview"
                                    width={1419}
                                    height={732}
                                    quality={100}
                                    className="rounded-md bg-white p-2 sm:p-8 shadow-2xl ring-1 ring-gray-900/10"
                                />
                            </div>
                        </div>
                    </div>

                </div>
            </MaxWidthWrapper>
        </>
    );
}
