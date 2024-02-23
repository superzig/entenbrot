'use client';
import MaxWidthWrapper from '~/app/_components/ui/MaxWidthWrapper';
import { InputFile } from '~/app/_components/ui/fileInput';
import { useState } from 'react';
import { readStudentsTestData } from '~/actions';
import { studentsSchema, type StudentsType } from '~/definitions';
import { Button } from '~/app/_components/ui/button';
import StudentsTable from '~/app/_components/ui/StudentsTable';

interface StudentsData {
  students: StudentsType;
  error: string | null;
}

export default function Page() {
  const [data, setData] = useState<StudentsData>({ students: [], error: null });
  const { students, error } = data;

  const onUpload = async () => {
    const data = await readStudentsTestData(); // TODO: replace with API CALL which returns JSON
    console.log(data);
    const validatedData = studentsSchema.safeParse(data);

    if (!validatedData.success) {
      setData({
        students: [],
        error: 'Das Format der Excel-Datei entspricht nicht den Vorgaben.',
      });
      console.log('not validate data', validatedData.error.flatten());
      return;
    }
    console.log('validated events');
    setData({ students: validatedData.data, error: null });
  };

  return (
    <>
      <MaxWidthWrapper className='mb-5 mt-10 flex flex-col'>
        <div className='mb-4'>
          <div className='mb-4 flex max-w-fit items-center justify-center space-x-2 overflow-hidden rounded-full border border-gray-200 bg-white px-7 py-2 shadow-md backdrop-blur transition-all hover:border-gray-300 hover:bg-white/50'>
            <p className='text-sm font-semibold text-gray-700'>Import</p>
          </div>
          <h1 className='text-5xl font-bold md:text-6xl lg:text-7xl'>
            <span className='text-blue-500'>Schülerliste</span> hochladen
          </h1>
          <p className='mt-5 max-w-prose text-zinc-700 sm:text-lg'>
            Bitte laden Sie die Datei mit den Informationen zur Schülern hoch.
            Es können bereits Wünsche vordefiniert sein.
          </p>
        </div>
        <InputFile onUpload={onUpload} errorMessage={error}></InputFile>
      </MaxWidthWrapper>
      <MaxWidthWrapper>
        <div className='mb-12 flex justify-end text-center align-bottom'>
          <Button
            variant='default'
            disabled={students.length == 0 || !students}
          >
            Nächster Schritt
          </Button>
        </div>
        <StudentsTable students={students} />
      </MaxWidthWrapper>
    </>
  );
}
