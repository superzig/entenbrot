import MaxWidthWrapper from '~/app/_components/ui/MaxWidthWrapper';
import Link from 'next/link';
import Image from 'next/image';
import logoImage from '../../../../public/logo.png';
import NavLinks from "~/app/_components/ui/NavLinks";

const Navbar = () => {
    return (
        <nav className='sticky inset-x-0 top-0 z-30 h-14 w-full border-b border-gray-200 bg-white/75 backdrop-blur-lg transition-all'>
            <MaxWidthWrapper>
                <div className='flex h-14 items-center justify-between border-b border-gray-200'>
                    <div className='flex  justify-start gap-x-2'>
                        <Image
                            alt='Logo Image'
                            src={logoImage}
                            quality={100}
                            style={{
                                width: '30px',
                            }}
                            className='pointer-events-none bg-contain'
                        />
                        <Link href='/' className='z-40 flex font-semibold'>
                            <span>Entenbrot.</span>
                        </Link>
                    </div>
                    <div className='items-center flex flex-row space-x-4'>
                        {/* Navbar links */}
                        <NavLinks />
                    </div>
                </div>
            </MaxWidthWrapper>
        </nav>
    );
};

export default Navbar;
