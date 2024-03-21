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
    firstName: z.string(),
    lastName: z.string(),
    choice1: z.number().optional().nullable(),
    choice2: z.number().optional().nullable(),
    choice3: z.number().optional().nullable(),
    choice4: z.number().optional().nullable(),
    choice5: z.number().optional().nullable(),
    choice6: z.number().optional().nullable(),
});

export const excelStudentKeyMap: Record<number, string> = {
    0: 'class',
    1: 'lastName',
    2: 'firstName',
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
    eventId: z.number(),
    company: z.string(),
    specialization: z.string().nullable(),
    maxParticipants: z.number(),
    amountEvents: z.number(),
    earliestTimeSlot: z.string(),
    // Add any additional fields here if necessary
});
export const eventsSchema = z.array(eventSchema);
export type EventType = z.infer<typeof eventSchema>;
export type EventsType = z.infer<typeof eventsSchema>;

export const excelEventKeyMap: Record<number, string> = {
    0: 'eventId',
    1: 'company',
    2: 'specialization',
    3: 'maxParticipants',
    4: 'amountEvents',
    5: 'earliestTimeSlot',
};

/*
 ############################
 ##     Rooms
 ############################
 */
export const roomSchema = z.object({
    roomId: z.union([z.string(), z.number().transform((n) => n.toString())]),
    capacity: z
        .number()
        .nullable()
        .transform((val: number | null) => val ?? 20),
});

export const excelRoomKeyMap: Record<number, string> = {
    0: 'roomId',
    1: 'capacity',
};

export type RoomType = z.infer<typeof roomSchema>;
export const roomsSchema = z.array(roomSchema);
export type RoomsType = z.infer<typeof roomsSchema>;

interface AlgorithmenResponse {
    data: {
        cacheKey: string|null;
        isError: boolean;
        cachedTime: number|null;
        data: AlgorithmenData|null;
    }
    error: string | null;
}

interface AlgorithmenData {
    attendanceList: object;
    organizationalPlan: object;
    studentSheet: object;
}

interface Student {
    class: string;
    lastName: string;
    firstName: string;
}

type Timeslots = Record<string, Student[]>;

export interface attendanceData {
    company: string;
    timeslots: Timeslots;
}

interface TimeslotRoomType {
    room: string;
    time: string;
    timeSlot: string;
}

export type roomsPlanType = Record<string, {
        company: string;
    specialization: string;
        timeslots: TimeslotRoomType[];
    }>;

interface Person {
    class: string;
    lastName: string;
    firstName: string;
}

type Timeslot = Record<string, Person[]>;

interface CompanyData {
    company: string;
    specialization: string;
    timeslots: Timeslot;
}

export type AttendancePlanType = Record<string, CompanyData>;


interface AssignmentDetails {
    room: number | string;
    company: string;
    specialization: string;
    eventId: string;
    isWish: number;
}

type Assignments = Record<string, AssignmentDetails>;

interface RoutingPlanStudent extends Person {
    assignments: Assignments;
}

export type RoutingPlanType = RoutingPlanStudent[];
