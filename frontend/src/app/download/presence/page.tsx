import React from 'react';
import * as XLSX from 'xlsx';

const Page = () => {
    const generateExcel = () => {
        // Daten vorbereiten
        const data = [
            { name: 'Max Mustermann', anwesend: '' },
            { name: 'Alina Müller', anwesend: '' },
            { name: 'Johannes Schmidt', anwesend: '' },
        ];

        // Workbook und Worksheet erstellen
        const workbook = XLSX.utils.book_new();
        const worksheet = XLSX.utils.json_to_sheet(data);

        // Titelzeile hinzufügen
        XLSX.utils.sheet_add_aoa(worksheet, [['Name', 'Anwesend']], {origin: 'A1'});

        // Worksheet zum Workbook hinzufügen
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Anwesenheitsliste');

        // Excel-Datei erstellen und herunterladen
        XLSX.writeFile(workbook, 'Anwesenheitsliste.xlsx');
    };

    return (
        <div>
            <h1>Excel-Generierung</h1>
            <button onClick={generateExcel}>Excel herunterladen</button>
        </div>
    );
};

export default Page;
