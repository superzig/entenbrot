import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from "~/app/_components/ui/card"
const SuccessInformation = () => {
    return (
        <div className="flex flex-center gap-3 text-center text-gray-300 flex-row justify-center items-center">
            <Card>
                <CardHeader>
                    <CardTitle className="text-lg font-semibold">Wünsche</CardTitle>
                </CardHeader>
                <CardContent className="text-gray-700 text-md">
                    <p>2132</p>
                </CardContent>
                <CardFooter>
                    <p>Card Footer</p>
                </CardFooter>
            </Card>
            <Card>
                <CardHeader>
                    <CardTitle>Card Title</CardTitle>
                    <CardDescription>Card Description</CardDescription>
                </CardHeader>
                <CardContent>
                    <p>Card Content</p>
                </CardContent>
                <CardFooter>
                    <p>Card Footer</p>
                </CardFooter>
            </Card>
        </div>
    );
}

export default SuccessInformation;