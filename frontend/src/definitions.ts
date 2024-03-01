import { z } from 'zod';

/*
 ############################
 ##     Students
 ############################
 */
const studentSchema = z.object({
  Klasse: z.string(),
  Name: z.string(),
  Vorname: z.string(),
  'Wahl 1': z.number().nullable(),
  'Wahl 2': z.number().nullable(),
  'Wahl 3': z.number().nullable(),
  'Wahl 4': z.number().nullable(),
  'Wahl 5': z.number().nullable(),
  'Wahl 6': z.number().nullable(),
});

export const studentsSchema = z.array(studentSchema);
export type StudentsType = z.infer<typeof studentsSchema>;

/*
 ############################
 ##     Events
 ############################
 */
const eventSchema = z.object({
  number: z.number(),
  company: z.string(),
  specialty: z.string().nullable(),
  participants: z.number(),
  eventMax: z.number(),
  earliestDate: z.number(),
  // Add any additional fields here if necessary
});

export const eventsSchema = z.array(eventSchema);
export type EventsType = z.infer<typeof eventsSchema>;

/*
 ############################
 ##     Rooms
 ############################
 */
const roomSchema = z.object({
  name: z.string(),
  capacity: z.number().optional().default(20),
});

export const roomsSchema = z.array(roomSchema);
export type RoomsType = z.infer<typeof roomsSchema>;
