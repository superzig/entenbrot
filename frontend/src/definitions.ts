import { z } from 'zod';

export type EntityType = 'Companies' | 'Students' | 'Rooms';

export interface DataResponse<T> {
    data: T[];
    error: string | null;
}

/*
 ############################
 ##     Students
 ############################
 */
export const studentSchema = z.object({
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

export const excelStudentKeyMap: Record<number, string> = {
    0: 'class',
    1: 'firstname',
    2: 'lastname',
    3: 'choice1',
    4: 'choice2',
    5: 'choice3',
    6: 'choice4',
    7: 'choice5',
    8: 'choice6',
};

export const studentsSchema = z.array(studentSchema);
export type StudentType = z.infer<typeof studentSchema>;
export type StudentsType = z.infer<typeof studentsSchema>;

/*
 ############################
 ##     Events
 ############################
 */
export const eventSchema = z.object({
    number: z.number(),
    company: z.string(),
    specialty: z.string().nullable(),
    participants: z.number(),
    eventMax: z.number(),
    earliestDate: z.string(),
    // Add any additional fields here if necessary
});
export const eventsSchema = z.array(eventSchema);
export type EventType = z.infer<typeof eventSchema>;
export type EventsType = z.infer<typeof eventsSchema>;

export const excelEventKeyMap: Record<number, string> = {
    0: 'number',
    1: 'company',
    2: 'specialty',
    3: 'participants',
    4: 'eventMax',
    5: 'earliestDate',
};

/*
 ############################
 ##     Rooms
 ############################
 */
export const roomSchema = z.object({
    name: z.union([z.string(), z.number().transform((n) => n.toString())]),
    capacity: z
        .number()
        .nullable()
        .transform((val: number | null) => val ?? 20),
});

export const excelRoomKeyMap: Record<number, string> = {
    0: 'name',
    1: 'capacity',
};

export type RoomType = z.infer<typeof roomSchema>;
export const roomsSchema = z.array(roomSchema);
export type RoomsType = z.infer<typeof roomsSchema>;
