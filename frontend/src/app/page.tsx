import {unstable_noStore as noStore} from "next/cache";

import {CreatePost} from "~/app/_components/create-post";
import {api} from "~/trpc/server";
import MaxWidthWrapper from "~/app/_components/MaxWidthWrapper";
import Link from "next/link";
import {ArrowRight} from "lucide-react";
import {buttonVariants} from "~/app/_components/ui/button";
import {InputFile} from "~/app/_components/ui/fileInput";

export default async function Home() {
    noStore();
    const hello = await api.post.hello.query({text: "from tRPC"});

    return (
        <>
            <MaxWidthWrapper className="mb-12 mt-28 sm:mt-40 flex flex-col">
                <div className="relative">
                    <div className="bg-[url('/landing-page-hero-event.png')] bg-contain bg-no-repeat bg-right w-[600px] h-[600px] absolute right-0"></div>
                    <div
                        className="mb-4 flex max-w-fit items-center justify-center space-x-2 overflow-hidden rounded-full border border-gray-200 bg-white px-7 py-2 shadow-md backdrop-blur transition-all hover:border-gray-300 hover:bg-white/50">
                        <p className="text-sm font-semibold text-gray-700">
                            $Nein$ ist jetzt öffentlich!
                        </p>
                    </div>
                    <h1 className="max-w-4xl text-5xl font-bold md:text-6xl lg:text-7xl">
                        Zuweisung von Schülern zu den
                        geeigneten  <span className="text-blue-500">Veranstaltungen</span>.
                    </h1>
                    <p className="mt-5 max-w-prose text-zinc-700 sm:text-lg">
                        $Nein$ ermöglicht die einfache Zuordnung von Schülern zu Veranstaltungen. Dokumente hochladen und direkt starten.
                    </p>

                    <Link className={buttonVariants({
                        size: 'lg',
                        className: "mt-5"
                    })} href="/upload" target="_blank">
                        Los geht`s <ArrowRight className="ml-2 h-5 w-5"/>
                    </Link>
                </div>
            </MaxWidthWrapper>
        </>
    );
}

async function CrudShowcase() {
    const latestPost = await api.post.getLatest.query();

    return (
        <div className="w-full max-w-xs">
            {latestPost ? (
                <p className="truncate">Your most recent post: {latestPost.name}</p>
            ) : (
                <p>You have no posts yet.</p>
            )}

            <CreatePost/>
        </div>
    );
}
