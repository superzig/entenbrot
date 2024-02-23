"use server"

import {z} from "zod";
import * as XLSX from 'xlsx';


const allowedExtensions = ['xls', 'xlsx', 'xlsm'];

// Custom validation for the file object
const fileSchema = z.object({
    file: z.object({
        name: z.string(),
        type: z.string(),
        size: z.number(),
    }).refine(file => allowedExtensions.some(ext => file.name.endsWith(`.${ext}`)), {
        message: "Invalid file extension. Only Excel files are allowed.",
    })
});


export async function uploadStudents(formData: FormData) {

    const response = await fetch('localhost:80/api/validate/upload1', {
        method: 'POST',
        body: formData,
    });

    const result = await response.json();
    console.log(result);
}