import MaxWidthWrapper from "~/app/_components/MaxWidthWrapper";
import Link from "next/link";
import {buttonVariants} from "~/app/_components/ui/button";
import {ArrowRight} from "lucide-react";
import {InputFile} from "~/app/_components/ui/fileInput";

export default async function Page() {


    return (
        <MaxWidthWrapper className="mb-12 mt-28 sm:mt-40 flex flex-col">
            <div className="flex gap-5">
                <InputFile>12321</InputFile>
                <InputFile>12321</InputFile>
                <InputFile>12321</InputFile>
            </div>

            <Link className={buttonVariants({
                size: 'lg',
                className: "mt-5"
            })} href="/upload" target="_blank">
                Los geht`s <ArrowRight className="ml-2 h-5 w-5"/>
            </Link>
        </MaxWidthWrapper>
    );
}