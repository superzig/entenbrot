import { z } from 'zod';

export type EntityType = 'Companies' | 'Students' | 'Rooms';
export interface DataResponse<T> {
  data: T;
  error: string | null;
}


/*
 ############################
 ##     Students
 ############################
 */
const studentSchema = z.object({
  class: z.string(),
  firstname: z.string(),
  lastname: z.string(),
  choose1: z.number().nullable(),
  choose2: z.number().nullable(),
  choose3: z.number().nullable(),
  choose4: z.number().nullable(),
  choose5: z.number().nullable(),
  choose6: z.number().nullable(),
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
  earliestDate: z.string(),
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
