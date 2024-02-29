'use client';
import { InputFile } from '~/app/_components/ui/fileInput';
import {FormEvent, useState} from 'react';
import {readEventsTestData} from '~/actions';
import { eventsSchema, type EventsType } from '~/definitions';
import { Button } from '~/app/_components/ui/button';
import EventsTable from '~/app/_components/ui/EventsTable';
import { useRouter } from 'next/navigation';

interface EventsData {
  events: EventsType;
  error: string | null;
}
export default function Page() {
  const [data, setData] = useState<EventsData>({ events: [], error: null });
  const { events, error } = data;
  const router = useRouter();

  const onUpload = async () => {
    const data = await readEventsTestData(); // TODO: replace with API CALL which returns JSON
    console.log(data);
    const validatedData = eventsSchema.safeParse(data);

    if (!validatedData.success) {
      setData({
        events: [],
        error: 'Das Format der Excel-Datei entspricht nicht den Vorgaben.',
      });
      console.log('not validate data', validatedData.error.flatten());
      return;
    }
    console.log('validated events');
    setData({ events: validatedData.data, error: null });
  };

  const handleNavigation = () => {
    if (events.length > 0 && events) {
      router.push('/upload/students');
    }
  };

  async function onSubmitEventUpload(event: FormEvent<HTMLFormElement>)
  {
    event.preventDefault();
    const formData = new FormData(event.currentTarget);
    console.log(formData)
    const response = await fetch('http://localhost:8000/api/returnCompanies', {
      method: 'POST',
      body: formData
    });

    if (response.ok) {
      console.log("Laravel data: ", response.json());
    } else {
      // It's good practice to handle the case where the response is not ok
      throw new Error(`HTTP error! Status: ${response.status}`);
    }
  }

  return (
    <form onSubmit={onSubmitEventUpload}>
      <div className='flex flex-col'>
        <div className='mb-4'>
          <div className='mb-4 flex max-w-fit items-center justify-center space-x-2 overflow-hidden rounded-full border border-gray-200 bg-white px-7 py-2 shadow-md backdrop-blur transition-all hover:border-gray-300 hover:bg-white/50'>
            <p className='text-sm font-semibold text-gray-700'>Import</p>
          </div>
          <h1 className='text-4xl font-bold md:text-6xl lg:text-7xl'>
            <span className='text-blue-500'>Veranstaltungsliste</span> hochladen
          </h1>
          <p className='mt-5 text-zinc-700 sm:text-lg md:max-w-prose'>
            Bitte laden Sie die Datei mit den Informationen zu den
            Veranstaltungen der Unternehmen hoch.
          </p>
        </div>
        <InputFile onUpload={onUpload} errorMessage={error}></InputFile>
      </div>
      <button type="submit">Submit</button>
      <div>
        <div className='mb-12 mt-4 flex justify-end text-center align-bottom'>
          <Button
            variant='default'
            disabled={events.length == 0 || !events}
            onClick={handleNavigation}
          >
            Nächster Schritt
          </Button>
        </div>
        <EventsTable events={events} />
      </div>
    </form>
  );
}
