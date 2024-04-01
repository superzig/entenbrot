'use server';

// write me an api call using fetch to the endpoint '/api/data/algorithmen' with the method 'POST' and the body containing the data from the argument
import {revalidatePath} from "next/cache";

export async function runAlgorithmen(formData: FormData) {
    const studentsData = formData.get('students');
    const roomsData = formData.get('rooms');
    const eventsData = formData.get('events');

    try {
        const response = await fetch(
            'http://localhost:8000/api/data/algorithmen',
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    students: studentsData,
                    rooms: roomsData,
                    events: eventsData,
                }),
            }
        );
        const data = await response.json();
        return { data: data, error: null };
    } catch (error) {
        let message = null;
        if (typeof error === 'string') {
            message = error.toUpperCase();
        } else if (error instanceof Error) {
            message = error.message;
        }

        return { data: [], error: message };
    }
}

export async function getAlgorithmenData(cacheKey: string) {
    try {
        const response = await fetch(
            'http://localhost:8000/api/data/algorithmen/' + cacheKey,
            {
                method: 'GET',
            }
        );
        const data = await response.json();

        if (response.status !== 200 || data.isError) {
            return {
                data: [],
                error:
                    data.message ?? 'Ein unerwarteter Fehler ist aufgetreten.',
            };
        }

        return { data: data, error: null };
    } catch (error) {
        let message = null;
        if (typeof error === 'string') {
            message = error.toUpperCase();
        } else if (error instanceof Error) {
            message = error.message;
        }

        return { data: [], error: message };
    }
}

export async function getAllAlgorithmenData() {
    try {
        const response = await fetch(
            'http://localhost:8000/api/data/algorithmen',
            {
                method: 'GET',
            }
        );
        const data = await response.json();

        if (response.status !== 200) {
            return {
                data: [],
                error: 'Ein unerwarteter Fehler ist aufgetreten.',
            };
        }

        return { data: data, error: null };
    } catch (error) {
        let message = null;
        if (typeof error === 'string') {
            message = error.toUpperCase();
        } else if (error instanceof Error) {
            message = error.message;
        }

        return { data: [], error: message };
    }
}

export async function deleteAlgorithmenData(cacheKey: string) {
    try {
        const response = await fetch(
            'http://localhost:8000/api/data/algorithmen/' + cacheKey,
            {
                method: 'DELETE',
            }
        );

        if (response.ok) {
            revalidatePath('/list');
            return { data: [], error: null };
        }

        throw new Error("Ein unerwarteter Fehler ist aufgetreten.");
    } catch (error) {
        let message = null;
        if (typeof error === 'string') {
            message = error.toUpperCase();
        } else if (error instanceof Error) {
            message = error.message;
        }

        return { data: [], error: message };
    }
}
