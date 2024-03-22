import { RoutingPlanType } from '~/definitions';
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '~/app/_components/ui/table';
import { Check } from 'lucide-react';

interface Props {
    routingPlan: RoutingPlanType;
}

const RoutingPlanTable = ({ routingPlan }: Props) => {
    return (
        <Table>
            <TableCaption>
                Eine Zusammenstellung der Schülerdaten aus der Excel-Tabelle.
            </TableCaption>
            <TableHeader>
                <TableRow>
                    <TableHead className='w-[100px]'>Klasse</TableHead>
                    <TableHead>Schüler</TableHead>
                    <TableHead>Zeitfenster</TableHead>
                    <TableHead>Raum</TableHead>
                    <TableHead>Veranstalter</TableHead>
                    <TableHead>Veranstaltung</TableHead>
                    <TableHead>Nr.</TableHead>
                    <TableHead className='text-right'>Wunsch erfüllt</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                {routingPlan.map((student, personIndex) =>
                    Object.entries(student.assignments).map(
                        ([time, assignment], index) => (
                            <TableRow
                                key={`${personIndex}-${time}`}
                                className={
                                    assignment.isWish == null
                                        ? 'border-l-2 border-l-yellow-300'
                                        : ''
                                }
                            >
                                <TableCell className='font-medium'>
                                    {index === 0 ? student.class : ''}
                                </TableCell>
                                <TableCell>
                                    {index === 0
                                        ? student.firstName +
                                          ' ' +
                                          student.lastName
                                        : ''}
                                </TableCell>
                                <TableCell>{time}</TableCell>
                                <TableCell>{assignment.room}</TableCell>
                                <TableCell>{assignment.company}</TableCell>
                                <TableCell>
                                    {assignment.specialization}
                                </TableCell>
                                <TableCell>{assignment.eventId}</TableCell>
                                <TableCell className='text-right'>
                                    {assignment.isWish !== null ? (
                                        <Check size={15} className='ms-auto' />
                                    ) : (
                                        ''
                                    )}
                                </TableCell>
                            </TableRow>
                        )
                    )
                )}
            </TableBody>
        </Table>
    );
};

export default RoutingPlanTable;
