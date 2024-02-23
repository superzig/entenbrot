import { z } from 'zod';

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

const eventSchema = z.object({
  'Nr. ': z.number(),
  Unternehmen: z.string(),
  Fachrichtung: z.string().nullable(),
  'Max. Teilnehmer': z.number(),
  'Max. Veranstaltungen': z.number(),
  'Frühester Zeitpunkt': z.string(),
  // Add any additional fields here if necessary
});

export const eventsSchema = z.array(eventSchema);
export type EventsType = z.infer<typeof eventsSchema>;
