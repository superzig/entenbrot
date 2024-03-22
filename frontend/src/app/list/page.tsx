"use client"
import {Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow,} from "~/app/_components/ui/table"
import MaxWidthWrapper from "~/app/_components/ui/MaxWidthWrapper";
import {useEffect, useState} from "react";
import {getAllAlgorithmenData} from "~/action";
import LoaderContainer from "~/app/_components/ui/LoaderContainer";
import {redirectToHome} from "~/app/summary/[cacheKey]/page";
import {toast} from "~/app/_components/ui/use-toast";
import {useRouter} from "next/navigation";
import {Check} from "lucide-react";


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

    if (response.data.length === 0) {
        return (<LoaderContainer/>);
    }

    return (
        <MaxWidthWrapper className="mt-6">
            <Table>
                <TableCaption>A list of your recent invoices.</TableCaption>
                <TableHeader>
                    <TableRow>
                        <TableHead className="w-[100px]">Invoice</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Method</TableHead>
                        <TableHead className="text-right">Amount</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>

                </TableBody>
            </Table>
        </MaxWidthWrapper>
    )
}

export default ListView;