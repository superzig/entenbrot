"use client"
import MaxWidthWrapper from '~/app/_components/ui/MaxWidthWrapper';
import SuccessScore from '~/app/_components/ui/successScore';
import {Tabs, TabsContent, TabsList, TabsTrigger,} from '~/app/_components/ui/tabs';
import {Button} from '~/app/_components/ui/button';
import EventsTable from '~/app/_components/ui/EventsTable';
import {useEffect, useState} from "react";
import {getAlgorithmenData} from "~/action";
import AttendanceTable from "~/app/_components/ui/AttendanceTable";

const Page = ({ params }: { params: { cacheKey: string } }) => {
    const cacheKey = params.cacheKey;

    const [response, setResponse] = useState<{data:[]|null, error: string|null}>({data: [], error: null})

    useEffect( () => {
        if (!cacheKey) {
            return;
        }
        getAlgorithmenData(cacheKey)
            .then((result) => setResponse(result))
            .catch(() => setResponse({data: [], error: "Ein unerwarteter Fehler ist aufgefallen."}))
    }, [cacheKey]);

    return (
        <MaxWidthWrapper className='mb-5 mt-10'>
            <div className='flex h-screen flex-col items-center'>
                <div className='my-10'>
                    <SuccessScore score={4.5} maxScore={5} />
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
                            <EventsTable events={[]} />
                        </TabsContent>
                        <TabsContent value='students_presence'>
                            {attendanceList ? (<AttendanceTable attendanceData={attendanceList} />) : 'Keine Daten gefunden.'}

                        </TabsContent>
                        <TabsContent value='events_rooms'>
                            <EventsTable events={[]} />
                        </TabsContent>
                    </Tabs>
                </div>
            </div>
        </MaxWidthWrapper>
    );
};

export default Page;
