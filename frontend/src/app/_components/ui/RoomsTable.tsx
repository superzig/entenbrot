import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '~/app/_components/ui/table';
import { type RoomsType } from '~/definitions';

interface Props {
  rooms: RoomsType;
}
const RoomsTable = ({ rooms }: Props) => {
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
        {rooms.map((room, index) => (
          <TableRow key={index}>
            <TableCell className='font-medium'>{room.name}</TableCell>
            <TableCell className='text-right'>{room.capacity}</TableCell>
          </TableRow>
        ))}
      </TableBody>
    </Table>
  );
};

export default RoomsTable;
