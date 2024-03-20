'use client';
import { InputFile } from '~/app/_components/ui/fileInput';
import { useState } from 'react';
import {
    type DataResponse,
    excelRoomKeyMap,
    roomSchema,
    type RoomType,
} from '~/definitions';
import { Button } from '~/app/_components/ui/button';
import RoomsTable from '~/app/_components/ui/RoomsTable';
import { useRouter } from 'next/navigation';
import { readExcelFile } from '~/lib/utils';
import useDataStore from "~/app/hooks/useDataStore";

export default function Page() {

    const addJson = useDataStore((state) => state.addJson);
    const [data, setData] = useState<DataResponse<RoomType>>({
        data: [],
        error: null,
    });
    const { data: rooms, error } = data;
    const router = useRouter();

    const onUpload = async (file: File) => {
        const data = await readExcelFile(file, roomSchema, excelRoomKeyMap);
        console.log(data);

        setData(data);

        if (!data.error) {
            addJson('rooms', data.data);
        }
    };

    const handleNavigation = () => {
        if (rooms.length > 0 && rooms) {
            router.push('/upload/events');
        }
    };

    return (
        <>
            <div className='flex flex-col'>
                <div className='mb-4'>
                    <div className='mb-4 flex max-w-fit items-center justify-center space-x-2 overflow-hidden rounded-full border border-gray-200 bg-white px-7 py-2 shadow-md backdrop-blur transition-all hover:border-gray-300 hover:bg-white/50'>
                        <p className='text-sm font-semibold text-gray-700'>
                            Import
                        </p>
                    </div>
                    <h1 className='text-5xl font-bold md:text-6xl lg:text-7xl'>
                        <span className='text-blue-500'>Raumliste</span>{' '}
                        hochladen
                    </h1>
                    <p className='mt-5 max-w-prose text-zinc-700 sm:text-lg'>
                        Bitte laden Sie die Datei mit den Informationen zu den
                        Räumen hoch. Kapazitäten der Räume können im Voraus
                        festgelegt werden.
                    </p>
                </div>
                <InputFile onUpload={onUpload} errorMessage={error}></InputFile>
            </div>
            <div>
                <div className='mb-12 mt-4 flex justify-end text-center align-bottom'>
                    <Button
                        variant='default'
                        disabled={rooms.length == 0 || !rooms}
                        onClick={handleNavigation}
                    >
                        Nächster Schritt
                    </Button>
                </div>
                <RoomsTable rooms={rooms} />
            </div>
        </>
    );
}
