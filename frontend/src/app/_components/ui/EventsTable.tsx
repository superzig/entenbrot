import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '~/app/_components/ui/table';
import { type EventsType } from '~/definitions';

interface Props {
    events: EventsType;
}
const EventsTable = ({ events }: Props) => {
    return (
        <Table>
            <TableCaption>
                Eine Zusammenstellung der Schülerdaten aus der Excel-Tabelle.
            </TableCaption>
            <TableHeader>
                <TableRow>
                    <TableHead className='w-[100px]'>Nr.</TableHead>
                    <TableHead>Unternehmen</TableHead>
                    <TableHead>Fachrichtung</TableHead>
                    <TableHead className='text-right'>
                        Max. Teilnehmer
                    </TableHead>
                    <TableHead className='text-right'>
                        Max. Veranstaltungen
                    </TableHead>
                    <TableHead className='text-right'>
                        Frühster Zeitpunkt
                    </TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                {events.map((event, index) => (
                    <TableRow key={index}>
                        <TableCell className='font-medium'>
                            {event.number}
                        </TableCell>
                        <TableCell>{event.company}</TableCell>
                        <TableCell>{event.specialty}</TableCell>
                        <TableCell className='text-right'>
                            {event.participants}
                        </TableCell>
                        <TableCell className='text-right'>
                            {event.eventMax}
                        </TableCell>
                        <TableCell className='text-right'>
                            {event.earliestDate}
                        </TableCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
};

export default EventsTable;
