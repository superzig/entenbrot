import { Input } from "~/app/_components/ui/input"
import { Label } from "~/app/_components/ui/label"

interface Props {
    children: string;
}
export function InputFile({children}: Props) {
    return (
        <div className="grid w-full max-w-sm items-center gap-1.5">
            <Label htmlFor="picture" className="text-2xl">{children}</Label>
            <Input id="picture" type="file" className="h-11 px-8"/>
        </div>
    )
}
