'use server';

import { promises as fs } from 'fs';
import {
  type EventsType,
  type RoomsType,
  type StudentsType,
} from '~/definitions';

/*
const allowedExtensions = ['xls', 'xlsx', 'xlsm'];

// Custom validation for the file object
const fileSchema = z.object({
  file: z
    .object({
      name: z.string(),
      type: z.string(),
      size: z.number(),
    })
    .refine(
      (file) => allowedExtensions.some((ext) => file.name.endsWith(`.${ext}`)),
      {
        message: 'Invalid file extension. Only Excel files are allowed.',
      }
    ),
});
*/
export async function readStudentsTestData(): Promise<StudentsType> {
  const file = await fs.readFile(
    process.cwd() + '/public/data/students.json',
    'utf8'
  );
  return JSON.parse(file) as StudentsType;
}

export async function readEventsTestData(): Promise<EventsType> {
  const file = await fs.readFile(
    process.cwd() + '/public/data/events.json',
    'utf8'
  );
  return JSON.parse(file) as EventsType;
}

export async function readRoomsTestData(): Promise<RoomsType> {
  const file = await fs.readFile(
    process.cwd() + '/public/data/rooms.json',
    'utf8'
  );
  return JSON.parse(file) as RoomsType;
}

export async function getTransformedEvents(formData: FormData)
{
  const response = await fetch('http://localhost:8000/api/returnCompanies', {
    method: 'POST',
    body: formData,
    headers: {
      contentType: "multipart/form-data"
    }
  })

  if (response.ok) {
    return response.json();
  }
}
