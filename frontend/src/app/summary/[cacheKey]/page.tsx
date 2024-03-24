'use client';
import MaxWidthWrapper from '~/app/_components/ui/MaxWidthWrapper';
import SuccessScore from '~/app/_components/ui/successScore';
import {
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
} from '~/app/_components/ui/tabs';
import { Button } from '~/app/_components/ui/button';
import { useEffect, useState } from 'react';
import { getAlgorithmenData } from '~/action';
import { toast, useToast } from '~/app/_components/ui/use-toast';
import { useRouter } from 'next/navigation';
import { type AppRouterInstance } from 'next/dist/shared/lib/app-router-context.shared-runtime';
import LoaderContainer from '~/app/_components/ui/LoaderContainer';
import RoomsPlanTable from '~/app/_components/ui/RoomsPlanTable';
import AttendancePlanTable from '~/app/_components/ui/AttendancePlanTable';
import RoutingPlanTable from '~/app/_components/ui/RoutingPlanTable';
import {downloadDocuments} from "~/lib/utils";

export const redirectToHome = (
    router: AppRouterInstance,
    message: string | null = null
) => {
    toast({
        title: 'Ein Fehler ist aufgetreten',
        description: message ?? 'Bitte laden Sie erneut alle Dateien hoch.',
        variant: 'destructive',
    });
    router.push('/');
};
const Page = ({ params }: { params: { cacheKey: string } }) => {
    const cacheKey = params.cacheKey;
    const router = useRouter();
    const [response, setResponse] = useState<{
        data: [];
        error: string | null;
    }>({ data: [], error: null });

    useEffect(() => {
        if (!cacheKey) {
            return;
        }
        getAlgorithmenData(cacheKey)
            .then((result) => setResponse(result))
            .catch(() =>
                setResponse({
                    data: [],
                    error: 'Ein unerwarteter Fehler ist aufgefallen.',
                })
            );
    }, [cacheKey]);

    if (response.error) {
        redirectToHome(router, response.error);
    }

    if (response.data.length === 0) {
        return <LoaderContainer />;
    }

    const responseData = response.data;
    const { isError, cachedTime, data } = responseData;
    if (data.length === 0 || isError) {
        redirectToHome(router, 'Es wurden keine Daten gefunden.');
    }
    const { attendanceList, organizationalPlan, score, studentSheet } = data;

    if (!attendanceList || !organizationalPlan || !score || !studentSheet) {
        redirectToHome(router, 'Es wurden keine Daten gefunden.');
        return;
    }

    return (
        <MaxWidthWrapper className='mb-5 mt-10'>
            <div className='flex h-screen flex-col items-center'>
                <div className='my-10'>
                    <SuccessScore
                        currentScore={score.reachedPoints}
                        totalScore={score.totalReachablePoints}
                        maxScore={score.maxReachablePoints}
                    />
                </div>
                <Button onClick={() => downloadDocuments(cacheKey)}>
                    Dokumente herunterladen
                </Button>

                <div className='my-10'>
                    <Tabs defaultValue='students'>
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
                            <RoutingPlanTable routingPlan={studentSheet} />
                        </TabsContent>
                        <TabsContent value='students_presence'>
                            <AttendancePlanTable
                                attendancePlan={attendanceList}
                            />
                        </TabsContent>
                        <TabsContent value='events_rooms'>
                            <RoomsPlanTable roomsPlan={organizationalPlan} />
                        </TabsContent>
                    </Tabs>
                </div>
            </div>
        </MaxWidthWrapper>
    );
};
export default Page;
