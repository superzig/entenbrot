import { Loader2 } from 'lucide-react';
import MaxWidthWrapper from "~/app/_components/ui/MaxWidthWrapper";
import Image from "next/image";
import logoImage from "../../../../public/logo.png";
const LoaderContainer = () => {
    return (
        <MaxWidthWrapper className="flex justify-center align-middle my-36 text-center">
            <div className="flex flex-col justify-center">
                <div className="mx-auto self-center">
                    <Image
                        alt='Events Image'
                        src={logoImage}
                        quality={100}
                        style={{
                            width: '60px',
                        }}
                        className='pointer-events-none hidden bg-contain md:block'
                    />
                </div>
                <h1 className="text-2xl text-bold text-gray-700 mt-1 mb-4">
                    Warte kurz, während wir die Ente füttern...
                </h1>
                <Loader2 className="animate-spin text-gray-300 mx-auto mt-3" size={20}/>
            </div>
        </MaxWidthWrapper>
    );
}

export default LoaderContainer;