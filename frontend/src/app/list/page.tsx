'use client';
import {Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow,} from '~/app/_components/ui/table';
import MaxWidthWrapper from '~/app/_components/ui/MaxWidthWrapper';
import {useEffect, useState} from 'react';
import {deleteAlgorithmenData, getAllAlgorithmenData} from '~/action';
import {toast} from '~/app/_components/ui/use-toast';
import {redirect, useRouter} from 'next/navigation';
import {ArrowRight, Files} from 'lucide-react';
import Link from 'next/link';
import {Button, buttonVariants} from '~/app/_components/ui/button';
import {downloadDocuments} from "~/lib/utils";

const getFormattedDate = (unixTimestamp: number) => {
    const date = new Date(unixTimestamp * 1000);

    // Use Intl.DateTimeFormat to format the date in German style
    const options = {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
    };

    return new Intl.DateTimeFormat('de-DE', options).format(date) + " Uhr";
};

const showErrorMessage = (error: string) => {
    toast({
        title: 'Ein Fehler ist aufgetreten',
        description: error,
        variant: 'destructive',
    });
}
const ListView = () => {
    const [response, setResponse] = useState<{
        data: [];
        error: string | null;
    }>({ data: [], error: null });
    const router = useRouter();

    const removeAlgoData = (cacheKey: string) => {
        deleteAlgorithmenData(cacheKey)
            .then(({error}) => {
                console.log("error", error)
                if (error == null) {
                    window.location.reload();
                    return;
                }

                showErrorMessage(error);
            })
            .catch((error: string) => {
                showErrorMessage(error);
            });
    };

    useEffect(() => {
        getAllAlgorithmenData()
            .then((result) => setResponse(result))
            .catch(() =>
                setResponse({
                    data: [],
                    error: 'Ein unerwarteter Fehler ist aufgefallen.',
                })
            );
    }, []);

    if (response.error) {
        toast({
            title: 'Ein Fehler ist aufgetreten',
            description: 'Ein unerwarteter Fehler ist aufgefallen.',
            variant: 'destructive',
        });
        router.push('/');
    }

    return (
        <MaxWidthWrapper className='mt-10'>
            <div className='mb-10'>
                <h1 className='text-3xl font-bold md:text-5xl'>
                    Gespeicherte{' '}
                    <span className='text-primary'>Auswertungen</span>{' '}
                </h1>
                <p className='mt-5 max-w-prose text-zinc-700 sm:text-lg'>
                    Entenbrot speichert die vorherigen Berechnungen des
                    Algorithmus automatisch. So können Sie jederzeit auf die
                    Ergebnisse zugreifen.
                </p>
            </div>
            <Table>
                <TableCaption>Eine Liste aller Auswertungen. Auswertungen, die älter als eine Woche sind, werden automatisch gelöscht.</TableCaption>
                <TableHeader>
                    <TableRow>
                        <TableHead>Auswertung</TableHead>
                        <TableHead>Cache-Key</TableHead>
                        <TableHead>Bis wann</TableHead>
                        <TableHead>Aktionen</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {Object.keys(response.data).map((key, index) => {
                        const algoData = response.data[key];

                        if (!algoData) {
                            return null;
                        }
                        return (
                            <TableRow key={`${key}-${index}`}>
                                <TableCell>
                                    #{index + 1}
                                </TableCell>
                                <TableCell>
                                    {algoData.cacheKey}
                                </TableCell>
                                <TableCell>
                                    {getFormattedDate(algoData.cachedTime)}
                                </TableCell>
                                <TableCell>
                                    <div className='flex gap-4'>
                                        <Link
                                            className={buttonVariants()}
                                            href={
                                                '/summary/' + algoData.cacheKey
                                            }
                                        >
                                            Auswertung{' '}
                                            <ArrowRight className='ml-2 h-5 w-5'/>
                                        </Link>
                                        <Button
                                            onClick={() => downloadDocuments(algoData.cacheKey)}
                                            variant='outline'
                                        >
                                            <Files className="mr-2"/>{' '}
                                            Herunterladen
                                        </Button>
                                        <Button
                                            onClick={() =>  removeAlgoData(algoData.cacheKey)}
                                            variant='destructive'
                                        >
                                            Löschen
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        );
                    })}
                </TableBody>
            </Table>
        </MaxWidthWrapper>
    );
};

export default ListView;
