import Image from 'next/image';
import laurelWreath from '../../../../public/noun-laurel-wreath.svg';

interface Props {
    score: number;
    maxScore: number;
}

const scoreToLabel = (score: number) => {
    if (score >= 4.5) {
        return 'Sehr gut';
    } else if (score >= 3.5) {
        return 'Gut';
    } else if (score >= 2.5) {
        return 'Befriedigend';
    } else if (score >= 1.5) {
        return 'Ausreichend';
    } else {
        return 'Mangelhaft';
    }
};

const SuccessScore = ({ score, maxScore }: Props) => {
    const percentageScore = (score / maxScore) * 100;

    const scoreLabel = scoreToLabel(score);

    return (
        <>
            <div className='flex flex-col items-center justify-center'>
                <div className='flex items-center justify-center'>
                    <Image
                        src={laurelWreath as string}
                        alt='Laurel Wreath'
                        className='w-[55px]'
                    />
                    <h1 className='mx-3 text-5xl font-bold text-gray-700'>
                        {score}
                    </h1>
                    <Image
                        src={laurelWreath as string}
                        alt='Laurel Wreath'
                        className='w-[55px] -scale-x-100 transform'
                    />
                </div>
                <p className='text-gray-400'>{scoreLabel}</p>
                <h2 className='mt-5 text-lg font-semibold'>
                    Erfolgsquote von {percentageScore}%{' '}
                </h2>
                <p className='text-md mt-1 w-3/5 text-center text-gray-700'>
                    Die Erfolgsquote gibt an, wie viele Schüler erfolgreich nach
                    Ihren Wünschen zugeordnet werden konnten.
                </p>
            </div>
        </>
    );
};

export default SuccessScore;
