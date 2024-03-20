import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '~/app/_components/ui/table';
import {type attendanceData} from '~/definitions';

interface Props {
    attendanceData: attendanceData[];
}
const AttendanceTable = ({ attendanceData }: Props) => {
    console.log(attendanceData);
    return (
        <Table>
            <TableCaption>
                Eine Zusammenstellung der Raumdaten aus der Excel-Tabelle.
            </TableCaption>
            <TableHeader>
                <TableRow>
                    <TableHead className='w-[100px]'>Raum</TableHead>
                    <TableHead className='text-right'>Kapazität</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                {attendanceData.map((attendance, index) => (
                    <TableRow key={index}>
                        <TableCell className='font-medium'>
                            {attendance.company}
                        </TableCell>
                        <TableCell className='text-right'>
                        </TableCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
};

export default AttendanceTable;
