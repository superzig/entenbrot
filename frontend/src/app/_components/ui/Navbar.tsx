import MaxWidthWrapper from '~/app/_components/ui/MaxWidthWrapper';
import Link from 'next/link';

const Navbar = () => {
  return (
    <nav className='sticky inset-x-0 top-0 z-30 h-14 w-full border-b border-gray-200 bg-white/75 backdrop-blur-lg transition-all'>
      <MaxWidthWrapper>
        <div className='flex h-14 items-center justify-between border-b border-zinc-200'>
          <Link href='/' className='z-40 flex font-semibold'>
            <span>Pathway.</span>
          </Link>

          <div className='hidden items-center space-x-4 sm:flex'>
            <>{/* Navbar links */}</>
          </div>
        </div>
      </MaxWidthWrapper>
    </nav>
  );
};

export default Navbar;
