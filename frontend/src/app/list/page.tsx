"use client"
import {Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow,} from "~/app/_components/ui/table"
import MaxWidthWrapper from "~/app/_components/ui/MaxWidthWrapper";
import {useEffect, useState} from "react";
import {getAllAlgorithmenData} from "~/action";
import LoaderContainer from "~/app/_components/ui/LoaderContainer";
import {redirectToHome} from "~/app/summary/[cacheKey]/page";
import {toast} from "~/app/_components/ui/use-toast";
import {useRouter} from "next/navigation";
import {ArrowRight, Check} from "lucide-react";
import Link from "next/link";
import {Button, buttonVariants} from "~/app/_components/ui/button";

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
        hour12: false
    };

    return new Intl.DateTimeFormat('de-DE', options).format(date);
}

const removeAlgoData = (cacheKey: string) => {
   try {
       fetch("http://localhost:8000/api/data/algorithmen/"+cacheKey, {
           method: 'DELETE',
       }).then(response => {
           if (response.ok) {
               toast({
                   title: "Daten erfolgreich gelöscht",
                   description: "Die Daten wurden erfolgreich gelöscht.",
               })
               return;
           }
           throw new Error();
       }).catch((error) => {
           toast({
               title: "Ein Fehler ist aufgetreten",
               description: (error instanceof Error) ? error.message : "Ein unerwarteter Fehler ist aufgetreten.",
               variant: "destructive",
           });
       })
   } catch (error) {
       toast({
           title: "Ein Fehler ist aufgetreten",
           description: (error instanceof Error) ? error.message : "Ein unerwarteter Fehler ist aufgetreten.",
           variant: "destructive",
       });
   }
}
const ListView = () => {

    const [response, setResponse] = useState<{ data: [], error: string | null }>({data: [], error: null})
    const router = useRouter();

    useEffect(() => {
        getAllAlgorithmenData()
            .then((result) => setResponse(result))
            .catch(() => setResponse({data: [], error: "Ein unerwarteter Fehler ist aufgefallen."}))
    }, []);

    console.log(response);

    if (response.error) {
        toast({
            title: "Ein Fehler ist aufgetreten",
            description: "Ein unerwarteter Fehler ist aufgefallen.",
            variant: "destructive",
        });
        router.push("/")
    }

    return (
        <MaxWidthWrapper className="mt-10">
            <div className="mb-10">
                <h1 className='text-3xl font-bold md:text-5xl'>
                    Gespeicherte{' '}
                    <span className='text-blue-500'>Durchläufe</span>{' '}

                </h1>
                <p className='mt-5 max-w-prose text-zinc-700 sm:text-lg'>
                    Entenbrot speichert die vorherigen Berechnungen des Algorithmus automatisch.
                    So können Sie jederzeit auf die Ergebnisse zugreifen.
                </p>
            </div>
            <Table>
                <TableCaption>Eine Auflistung aller Durchläufe.</TableCaption>
                <TableHeader>

                    <TableRow>
                        <TableHead>Cache-Key</TableHead>
                        <TableHead>Bis wann</TableHead>
                        <TableHead>Aktionen</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {Object.keys(response.data).map((key, index) => {
                        const algoData = response.data[key];
                        return (<TableRow key={`${key}-${index}`}>
                            <TableCell className="font-medium">{algoData.cacheKey}</TableCell>
                            <TableCell>{getFormattedDate(algoData.cachedTime)}</TableCell>
                            <TableCell>
                                <div className="flex gap-4">
                                    <Link
                                        className={buttonVariants()}
                                        href={'/summary/' + algoData.cacheKey}
                                    >
                                        Auswertung
                                    </Link>
                                    <Button onClick={() => removeAlgoData(algoData.cacheKey)} variant="destructive">
                                        Löschen
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>)
                    })}
                </TableBody>
            </Table>
        </MaxWidthWrapper>
    )
}

export default ListView;