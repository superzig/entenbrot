import Image from "next/image";
import laurelWreath from "../../../../public/noun-laurel-wreath.svg";

interface Props {
    score: number;
    maxScore: number;
}

const scoreToLabel = (score: number) => {
    if (score >= 4.5) {
        return "Sehr gut";
    } else if (score >= 3.5) {
        return "Gut";
    } else if (score >= 2.5) {
        return "Befriedigend";
    } else if (score >= 1.5) {
        return "Ausreichend";
    } else {
        return "Mangelhaft";
    }
}

const SuccessScore = ({score, maxScore}: Props) => {

   const percentageScore = (score / maxScore) * 100;

   const scoreLabel = scoreToLabel(score);

  return (
      <>
          <div className="flex justify-center flex-col items-center">
              <div className="flex justify-center items-center">
                  <Image src={laurelWreath as string} alt="Laurel Wreath" className="w-[55px]"/>
                  <h1 className="text-5xl font-bold text-gray-700 mx-3">{score}</h1>
                  <Image src={laurelWreath as string} alt="Laurel Wreath" className="w-[55px] transform -scale-x-100"/>
              </div>
              <p className="text-gray-400">{scoreLabel}</p>
              <h2 className="mt-5 text-lg font-semibold">Erfolgsquote von {percentageScore}% </h2>
                <p className="text-md text-gray-700 w-3/5 text-center mt-1">
                    Die Erfolgsquote gibt an, wie viele Schüler erfolgreich nach Ihren Wünschen zugeordnet werden konnten.
                </p>
          </div>
      </>
  );
};

export default SuccessScore;
