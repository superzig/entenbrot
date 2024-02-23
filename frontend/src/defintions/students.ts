import {z} from "zod";

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