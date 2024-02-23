import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "~/app/_components/ui/table"
import {StudentsType} from "~/definitions";

interface Props {
    students: StudentsType
}
const StudentsTable = ({students}: Props) => {

    return (
        <Table>
            <TableCaption>Eine Zusammenstellung der Schülerdaten aus der Excel-Tabelle.</TableCaption>
            <TableHeader>
                <TableRow>
                    <TableHead className="w-[100px]">Klasse</TableHead>
                    <TableHead>Vorname</TableHead>
                    <TableHead>Nachname</TableHead>
                    <TableHead className="text-right">Wahl 1</TableHead>
                    <TableHead className="text-right">Wahl 2</TableHead>
                    <TableHead className="text-right">Wahl 3</TableHead>
                    <TableHead className="text-right">Wahl 4</TableHead>
                    <TableHead className="text-right">Wahl 5</TableHead>
                    <TableHead className="text-right">Wahl 6</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                {students.map((student, index) => (
                    <TableRow key={index}>
                        <TableCell className="font-medium">{student.Klasse}</TableCell>
                        <TableCell>{student.Vorname}</TableCell>
                        <TableCell>{student.Name}</TableCell>
                        <TableCell className="text-right">{student["Wahl 1"]}</TableCell>
                        <TableCell className="text-right">{student["Wahl 2"]}</TableCell>
                        <TableCell className="text-right">{student["Wahl 3"]}</TableCell>
                        <TableCell className="text-right">{student["Wahl 4"]}</TableCell>
                        <TableCell className="text-right">{student["Wahl 5"]}</TableCell>
                        <TableCell className="text-right">{student["Wahl 6"]}</TableCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    )
}

export default StudentsTable;