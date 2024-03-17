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
    choice1: z.number().optional().nullable(),
    choice2: z.number().optional().nullable(),
    choice3: z.number().optional().nullable(),
    choice4: z.number().optional().nullable(),
    choice5: z.number().optional().nullable(),
    choice6: z.number().optional().nullable(),
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
    capacity: z
        .number()
        .nullable()
        .transform((val: number | null) => val ?? 20),
});

export const roomsSchema = z.array(roomSchema);
export type RoomsType = z.infer<typeof roomsSchema>;
