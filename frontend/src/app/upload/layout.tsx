import '~/styles/globals.css';
import React from 'react';
import MaxWidthWrapper from '~/app/_components/ui/MaxWidthWrapper';

export const metadata = {
  title: 'Upload files',
  description: 'The official Pathway software.',
};

export default function Layout({ children }: { children: React.ReactNode }) {
  return (
    <MaxWidthWrapper className='mb-5 mt-10'>
      <div className='flex h-screen flex-col'>
        <div></div>
        <div className='flex-grow'>{children}</div>
      </div>
    </MaxWidthWrapper>
  );
}
