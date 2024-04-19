import { Loader2 } from 'lucide-react';
import MaxWidthWrapper from '~/app/_components/ui/MaxWidthWrapper';
import Image from 'next/image';
import logoImage from '../../../../public/logo.png';
const LoaderContainer = () => {
    return (
        <MaxWidthWrapper className='my-36 flex justify-center text-center align-middle'>
            <div className='flex flex-col justify-center'>
                <div className='mx-auto self-center'>
                    <Image
                        alt='Events Image'
                        src={logoImage}
                        quality={100}
                        style={{
                            width: '300px',
                        }}
                        priority={true}
                        className='pointer-events-none mx-auto hidden bg-contain md:block'
                    />
                </div>
                <h1 className='text-bold mb-4 mt-1 text-2xl'>
                    Warte kurz, während wir die Ente füttern...
                </h1>
                <Loader2
                    className='mx-auto mt-3 animate-spin text-gray-300'
                    size={20}
                />
            </div>
        </MaxWidthWrapper>
    );
};

export default LoaderContainer;
