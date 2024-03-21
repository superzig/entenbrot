import Image from 'next/image';
import laurelWreath from '../../../../public/noun-laurel-wreath.svg';
import {Tabs, TabsContent, TabsList, TabsTrigger} from "~/app/_components/ui/tabs";
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from "~/app/_components/ui/accordion"

interface Props {
    currentScore: number;
    totalScore: number;
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

const SuccessScore = ({ currentScore, totalScore, maxScore }: Props) => {


    const totalScorePercentage = Math.round( (currentScore / totalScore) * 100);
    const maxScorePercentage = Math.round((currentScore / maxScore) * 100);

    // transform the current score to an number between 0 and 5
    const totalScoreLabel = scoreToLabel(( (currentScore / totalScore) * 5));
    const maxScoreLabel = scoreToLabel(( (currentScore / maxScore) * 5));

    return (
        <>
            <div className='flex flex-col items-center justify-center'>
                <div className='flex-col items-center justify-center'>
                    <Tabs
                        defaultValue='total'
                        className='flex flex-col justify-center'
                    >
                        <TabsList>
                            <TabsTrigger value='total'>
                                Einfach
                            </TabsTrigger>
                            <TabsTrigger value='max'>
                                Real
                            </TabsTrigger>
                        </TabsList>
                        <TabsContent value='total' className="self-center">
                            <div className="flex items-center justify-center">
                                <Image
                                    src={laurelWreath as string}
                                    alt='Laurel Wreath'
                                    className='w-[55px]'
                                />
                                <h1 className='mx-3 text-5xl font-bold text-gray-700 relative'>
                                    {totalScorePercentage}
                                    <span className="text-sm text-gray-400 font-normal">%</span>
                                </h1>
                                <Image
                                    src={laurelWreath as string}
                                    alt='Laurel Wreath'
                                    className='w-[55px] -scale-x-100 transform'
                                />
                            </div>
                            <p className="text-md text-gray-400 text-center mx-auto">{totalScoreLabel}</p>
                            <div className="mt-5 w-1/2 self-center mx-auto text-center">
                                <p className="font-semibold mb-2">
                                    Erfolgsquote erklärt
                                </p>
                                <p>
                                    Die Erfolgsquote zeigt den Prozentsatz der Schüler, die gemäß ihren Wünschen
                                    platziert wurden.
                                    Die Wünsche werden mit Punkten absteigend (6 bis 1) bewertet, wobei 20 Punkte das
                                    Maximum sind. Die
                                    Quote berechnet sich aus den erreichten im Verhältnis zu den maximal möglichen
                                    Punkten.
                                </p>
                                <br/>
                                <p>Ungeäußerte Wünsche fließen nicht in die Berechnung ein.</p>
                            </div>
                        </TabsContent>
                        <TabsContent value='max' className="md:w-3/5 self-center">
                        <div className="flex items-center justify-center">
                                <Image
                                    src={laurelWreath as string}
                                    alt='Laurel Wreath'
                                    className='w-[55px]'
                                />
                                <h1 className='mx-3 text-5xl font-bold text-gray-700 relative'>
                                    {maxScorePercentage}
                                    <span className="text-sm text-gray-400 font-normal">%</span>
                                </h1>
                                <Image
                                    src={laurelWreath as string}
                                    alt='Laurel Wreath'
                                    className='w-[55px] -scale-x-100 transform'
                                />
                            </div>
                            <p className="text-md text-gray-400 text-center mx-auto">{maxScoreLabel}</p>
                            <div className="mt-5 self-center mx-auto text-center">
                                <p className="font-semibold mb-2">
                                    Erfolgsquote erklärt
                                </p>
                                <p>
                                    Die Erfolgsquote zeigt den Prozentsatz der Schüler, die gemäß ihren Wünschen
                                    platziert wurden.
                                    Die Wünsche werden mit Punkten absteigend (6 bis 1) bewertet, wobei 20 Punkte das
                                    Maximum sind. Die
                                    Quote berechnet sich aus den erreichten im Verhältnis zu den maximal möglichen
                                    Punkten.
                                </p>
                                <br/>
                                <p>Ungeäußerte Wünsche werden als nicht erfüllt betrachtet und in die Berechnung einbezogen.</p>
                            </div>
                        </TabsContent>
                    </Tabs>
                </div>


            </div>
        </>
    );
};

export default SuccessScore;
