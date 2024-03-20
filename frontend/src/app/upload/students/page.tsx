'use client';
import { InputFile } from '~/app/_components/ui/fileInput';
import { useState } from 'react';
import {
    type DataResponse,
    excelStudentKeyMap,
    studentSchema,
    type StudentType,
} from '~/definitions';
import { Button } from '~/app/_components/ui/button';
import StudentsTable from '~/app/_components/ui/StudentsTable';
import { readExcelFile } from '~/lib/utils';

export default function Page() {
    const [data, setData] = useState<DataResponse<StudentType>>({
        data: [],
        error: null,
    });
    const { data: students, error } = data;

    const onUpload = async (file: File) => {
        const data = await readExcelFile(
            file,
            studentSchema,
            excelStudentKeyMap
        );
        setData(data);
    };

    const handleNavigation = () => {
        if (students.length > 0 && students) {
            alert('finished steps');
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
                        <span className='text-blue-500'>Schülerliste</span>{' '}
                        hochladen
                    </h1>
                    <p className='mt-5 max-w-prose text-zinc-700 sm:text-lg'>
                        Bitte laden Sie die Datei mit den Informationen zur
                        Schülern hoch. Es können bereits Wünsche vordefiniert
                        sein.
                    </p>
                </div>
                <InputFile onUpload={onUpload} errorMessage={error}></InputFile>
            </div>
            <div>
                <div className='mb-12 mt-4 flex justify-end text-center align-bottom'>
                    <Button
                        variant='default'
                        disabled={students.length == 0 || !students}
                        onClick={handleNavigation}
                    >
                        Weiter zur Übersicht
                    </Button>
                </div>
                <StudentsTable students={students} />
            </div>
        </>
    );
}
