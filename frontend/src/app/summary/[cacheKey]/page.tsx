"use client"
import MaxWidthWrapper from '~/app/_components/ui/MaxWidthWrapper';
import SuccessScore from '~/app/_components/ui/successScore';
import {Tabs, TabsContent, TabsList, TabsTrigger,} from '~/app/_components/ui/tabs';
import {Button} from '~/app/_components/ui/button';
import EventsTable from '~/app/_components/ui/EventsTable';
import {useEffect, useState} from "react";
import {getAlgorithmenData} from "~/action";
import AttendanceTable from "~/app/_components/ui/AttendanceTable";
import {toast} from "~/app/_components/ui/use-toast";
import {useRouter} from "next/navigation";
import {type AppRouterInstance} from "next/dist/shared/lib/app-router-context.shared-runtime";

const redirectToHome = (router: AppRouterInstance, message: string|null = null ) => {
    toast({
        title: "Ein Fehler ist aufgetreten",
        description: message ?? "Bitte laden Sie erneut alle Dateien hoch.",
        variant: "destructive",
    });
    router.push("/")
}
const Page = ({ params }: { params: { cacheKey: string } }) => {
    const cacheKey = params.cacheKey;
    const router = useRouter();
    const [response, setResponse] = useState<{data:[], error: string|null}>({data: [], error: null})

    useEffect( () => {
        if (!cacheKey) {
            return;
        }
        getAlgorithmenData(cacheKey)
            .then((result) => setResponse(result))
            .catch(() => setResponse({data: [], error: "Ein unerwarteter Fehler ist aufgefallen."}))
    }, [cacheKey]);

    if (response.error) {
        redirectToHome(router, response.error);
    }

    if (response.data.length === 0) {
        return ("Loading...");
    }

    const responseData = response.data;

    const {isError, cachedTime, data} = responseData;
    if (data.length === 0 || isError) {
        redirectToHome(router, "Es wurden keine Daten gefunden.");
    }
    const {attendanceList, organizationalPlan, score, studentSheet} = data;

    if (!attendanceList || !organizationalPlan || !score || !studentSheet) {
        redirectToHome(router, "Es wurden keine Daten gefunden.");
    }
    console.log(score)
    return (
        <MaxWidthWrapper className='mb-5 mt-10'>
            <div className='flex h-screen flex-col items-center'>
                <div className='my-10'>
                    <SuccessScore currentScore={score.reachedPoints} totalScore={score.totalReachablePoints} maxScore={score.maxReachablePoints} />
                </div>
                <Button>Dokumente herunterladen</Button>

                <div className='my-10'>
                    <Tabs
                        defaultValue='students'
                        className='flex flex-col justify-center'
                    >
                        <TabsList>
                            <TabsTrigger value='students'>
                                Laufzettel
                            </TabsTrigger>
                            <TabsTrigger value='students_presence'>
                                Anwesenheitsliste
                            </TabsTrigger>
                            <TabsTrigger value='events_rooms'>
                                Veranstaltungen
                            </TabsTrigger>
                        </TabsList>
                        <TabsContent value='students'>
                        </TabsContent>
                        <TabsContent value='students_presence'>

                        </TabsContent>
                        <TabsContent value='events_rooms'>
                        </TabsContent>
                    </Tabs>
                </div>
            </div>
        </MaxWidthWrapper>
    );
};

export default Page;