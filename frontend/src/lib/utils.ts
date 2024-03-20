import {type ClassValue, clsx} from 'clsx';
import {twMerge} from 'tailwind-merge';
import readXlsxFile from "read-excel-file";
import {type z, type ZodObject, type ZodRawShape, type ZodTypeAny} from "zod";
import {type DataResponse} from "~/definitions";

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export const readExcelFile = <T extends ZodRawShape>(
    file: File,
    schema: ZodObject<T, "strip", ZodTypeAny>,
    keyMapping: Record<number, string>
): Promise<DataResponse<z.infer<typeof schema>>> => {
    return new Promise((resolve) => {
        readXlsxFile(file).then((rows) => {
            const data: z.infer<typeof schema>[] = [];
            let error: string | null = null;

            for (let rowIndex = 1; rowIndex < rows.length; rowIndex++) {
                const rowData: Record<string, unknown> = {};
                const row = rows[rowIndex];

                if (!row) {
                    continue;
                }
                row.forEach((cell, index) => {
                    const key = keyMapping[index];
                    if (key) {
                        rowData[key] = cell;
                    }
                });

                const parsedRow = schema.safeParse(rowData);
                if (!parsedRow.success) {
                    error = "Das Format der Excel-Datei entspricht nicht den Vorgaben.";
                    console.log("zod errors:", parsedRow.error.flatten());
                    break
                }

                data.push(parsedRow.data);
            }

            resolve({
                error,
                data: error ? [] : data,
            });
        }).catch((readError: Error) => {
            // Handle file read errors
            resolve({
                error: readError.message || 'Ein unerwarteter Fehler ist beim Einlesen der Datei aufgetreten.',
                data: [],
            });
        });
    });
};
