'use server';

import { promises as fs } from 'fs';
import {
    type DataResponse,
    type EntityType,
    eventsSchema,
    type EventsType,
    roomsSchema,
    type RoomsType,
    studentsSchema,
    type StudentsType,
} from '~/definitions';
import { type ZodSchema } from 'zod';

// Define a mapping from entity type to corresponding schema
const schemaMap: Record<EntityType, ZodSchema> = {
    Companies: eventsSchema,
    Students: studentsSchema,
    Rooms: roomsSchema,
};

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

export async function transformEntities<T>(
    entityType: EntityType,
    formData: FormData
): Promise<DataResponse<T>> {
    try {
        const endpoint = `http://localhost:8000/api/validate/return${entityType}`;
        const response = await fetch(endpoint, {
            method: 'POST',
            body: formData,
        });

        // Determine the correct schema based on the entity type
        const schema = schemaMap[entityType];
        const data = (await response.json()) as T;
        const validatedData = schema.safeParse(data);

        if (!validatedData.success) {
            console.log(
                'Failed validating:',
                data,
                endpoint,
                validatedData.error.flatten()
            );
            return {
                data: [] as T,
                error: `Das Format der Excel-Datei entspricht nicht den Vorgaben für ${entityType}.`,
            };
        }
        console.log('succesfully validated', data);
        // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
        return { data: validatedData.data, error: null };
    } catch (error) {
        let message = null;
        if (typeof error === 'string') {
            message = error.toUpperCase();
        } else if (error instanceof Error) {
            message = error.message;
        }

        return { data: [] as T, error: message };
    }
}
