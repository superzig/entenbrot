'use server';

import { promises as fs } from 'fs';
import {
  type EventsType,
  type RoomsType,
  type StudentsType,
} from '~/definitions';
import {FormEvent} from "react";

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
