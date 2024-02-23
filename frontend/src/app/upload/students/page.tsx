"use client";
import MaxWidthWrapper from "~/app/_components/ui/MaxWidthWrapper";
import {InputFile} from "~/app/_components/ui/fileInput";
import {useState} from "react";
import {uploadStudents} from "~/actions";
import {Button} from "~/app/_components/ui/button";

export default function Page() {

    const [students, setStudents] = useState<[]| null>(null);
    console.log(students)
    const onUpload = (file: File) => {
    }
    return (
        <>
            <MaxWidthWrapper className="mb-12 mt-28 flex flex-col">
                <div className="mb-4">
                    <div
                        className="mb-4 flex max-w-fit items-center justify-center space-x-2 overflow-hidden rounded-full border border-gray-200 bg-white px-7 py-2 shadow-md backdrop-blur transition-all hover:border-gray-300 hover:bg-white/50">
                        <p className="text-sm font-semibold text-gray-700">
                            Import
                        </p>
                    </div>
                    <h1 className="text-5xl font-bold md:text-6xl lg:text-7xl"><span className="text-blue-500">
                        Schülerliste</span> {(students === null) ? 'hochladen' : 'bearbeiten'}
                    </h1>
                    <p className="mt-5 max-w-prose text-zinc-700 sm:text-lg">
                        Bitte laden Sie die Datei mit den Informationen zur Schülern hoch. Es können bereits Wünsche
                        vordefiniert sein.
                    </p>
                </div>
                <form action={uploadStudents}>
                    <InputFile onUpload={onUpload}></InputFile>
                    <Button type="submit">Submit</Button>
                </form>
            </MaxWidthWrapper>
        <MaxWidthWrapper>
            12321
        </MaxWidthWrapper>
        </>
    );
}