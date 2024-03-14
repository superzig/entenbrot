import MaxWidthWrapper from "~/app/_components/ui/MaxWidthWrapper";
import SuccessScore from "~/app/_components/ui/successScore";
import SuccessInformation from "~/app/_components/ui/successInformation";

const Page = () => {
    return (
<MaxWidthWrapper className='mb-5 mt-10'>
      <div className='flex h-screen flex-col'>
        <div className='my-10'>
          <SuccessScore score={4.5} maxScore={5} />
        </div>
          <SuccessInformation></SuccessInformation>
      </div>
    </MaxWidthWrapper>
    )
}

export default Page;