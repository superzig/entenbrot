import { roomsPlanType } from '~/definitions';
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '~/app/_components/ui/table';

interface Props {
    roomsPlan: roomsPlanType;
}
const RoomsPlanTable = ({ roomsPlan }: Props) => {
    return (
        <Table>
            <TableCaption>
                Eine Zusammenstellung der Schülerdaten aus der Excel-Tabelle.
            </TableCaption>
            <TableHeader>
                <TableRow>
                    <TableHead className='w-[100px]'>Firma</TableHead>
                    <TableHead>Raum</TableHead>
                    <TableHead>Zeitfenster</TableHead>
                    <TableHead className='text-right'>Zeitslot</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                {Object.keys(roomsPlan).map((key) => {
                    const companyData = roomsPlan[key];
                    if (!companyData?.timeslots) {
                        return null;
                    }
                    return companyData.timeslots.map((slotData, index) => (
                        <TableRow key={`${key}-${index}`}>
                            <TableCell className='font-medium'>
                                {index === 0
                                    ? companyData.company +
                                      ` (${companyData.specialization})`
                                    : ''}
                            </TableCell>
                            <TableCell>{slotData.room}</TableCell>
                            <TableCell>{slotData.time}</TableCell>
                            <TableCell className='text-right'>
                                {slotData.timeSlot}
                            </TableCell>
                        </TableRow>
                    ));
                })}
            </TableBody>
        </Table>
    );
};

export default RoomsPlanTable;
