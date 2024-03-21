import {AttendancePlanType} from "~/definitions";
import {Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow} from "~/app/_components/ui/table";

interface Props {
    attendancePlan: AttendancePlanType
}
const RoomsPlanTable = ({ attendancePlan }: Props) => {
    return (
        <Table>
            <TableCaption>
                Eine Zusammenstellung der Schülerdaten aus der Excel-Tabelle.
            </TableCaption>
            <TableHeader>
                <TableRow>
                    <TableHead className='w-[100px]'>Firma</TableHead>
                    <TableHead>Klasse</TableHead>
                    <TableHead>Schüler</TableHead>
                    <TableHead className='text-right'>
                        Zeitslot
                    </TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                {Object.keys(attendancePlan).map((key) => {
                    const companyData = attendancePlan[key];
                    return companyData?.timeslots
                        ? Object.entries(companyData.timeslots).map(([timeslot, attendanceData]) => (
                            attendanceData.map((person, index) => (
                                <TableRow key={`${key}-${timeslot}-${index}`}>
                                    <TableCell className="font-medium">{index === 0 ? (companyData.company+` (${companyData.specialization})`) : ''}</TableCell>
                                    <TableCell>{person.class}</TableCell>
                                    <TableCell>{person.firstName} {person.lastName}</TableCell>
                                    <TableCell className="font-medium font-right">{index === 0 ? timeslot : ''}</TableCell>
                                </TableRow>
                            ))
                        ))
                        : null;
                })}
            </TableBody>
        </Table>
    )
}

export default RoomsPlanTable;