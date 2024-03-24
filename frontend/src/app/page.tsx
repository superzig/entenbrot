"use client";
import MaxWidthWrapper from '~/app/_components/ui/MaxWidthWrapper';
import Link from 'next/link';
import { ArrowRight } from 'lucide-react';
import { buttonVariants } from '~/app/_components/ui/button';
import Image from 'next/image';
import logoImage from '../../public/logo.png';
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
                        <div className='mb-4 flex max-w-fit items-center justify-center space-x-2 overflow-hidden rounded-full border border-gray-200 bg-white px-7 py-2 shadow-md backdrop-blur transition-all hover:border-gray-300 hover:bg-white/50'>
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
                            <ArrowRight className='ml-2 h-5 w-5' />
                        </Link>
                    </div>
                </div>
            </MaxWidthWrapper>
        </>
    );
}
