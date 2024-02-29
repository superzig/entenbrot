"use client"
import {usePathname} from 'next/navigation'
import React from 'react';
import {cn} from "~/lib/utils";

const steps = [
    {
        urlMatch: "rooms",
        label: "Räume",
        number: 1
    },
    {
        urlMatch: "events",
        label: "Veranstaltungen",
        number: 2
    },
    {
        urlMatch: "students",
        label: "Schüler & Wünsche",
        number: 3
    },
]

const getActiveStep = (pathname: string) => steps.find(({ urlMatch }) => pathname.includes(urlMatch))?.number ?? 0;

const getMaxStepNumber = () => Math.max(...steps.map(({ number }) => number));

const StepperComponent = () => {
    const pathname = usePathname();
    const activeStep = getActiveStep(pathname);
    const maxStepNumber = getMaxStepNumber();

    return (
        <ol className="flex items-center w-full text-sm font-medium text-center text-gray-500 dark:text-gray-400 sm:text-base">
            {steps.map(({ number, label }) => {
                const isActive = activeStep >= number;
                const isCompleted = activeStep > number;
                const hasNextStep = maxStepNumber > number;

                return (
                    <li
                        key={number}
                        className={cn(
                            "flex items-center",
                            isActive ? 'text-blue-600 dark:text-blue-500' : '',
                            hasNextStep ? isCompleted ? 'dark:after:border-blue-500 after:border-blue-600' : 'dark:after:border-gray-700 after:border-gray-200' : '',
                            hasNextStep ? "md:w-full sm:after:content-[''] after:w-full after:h-1 after:border-b after:hidden sm:after:inline-block after:mx-6 xl:after:mx-10" : ''
                        )}
                    >
            <span className="flex items-center text-nowrap">
              {isCompleted ? (
                  <svg className="w-3.5 h-3.5 sm:w-4 sm:h-4 me-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                  </svg>
              ) : (
                  <span className="me-2">{number}</span>
              )}
                {label}
            </span>
                    </li>
                );
            })}
        </ol>
    );
};

const Stepper = React.memo(StepperComponent);
Stepper.displayName = 'Stepper';

export default Stepper;