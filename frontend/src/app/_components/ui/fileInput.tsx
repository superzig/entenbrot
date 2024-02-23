import {useState} from "react";
import {cn} from "~/lib/utils";

interface Props {
    onUpload: (file: File) => void;
}

export function InputFile({onUpload}: Props) {

    const [error, setError] = useState<string | null>(null);
    const [file, setFile] = useState<File | null>(null);

    const handleFileChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files ? event.target.files[0] : null;

        if (!file) {
            setError('Es konnte keine Datei gefunden werden')
            return;
        }
        setFile(file);
        const fileName = file.name;

        const fileExtension = fileName.slice(((fileName.lastIndexOf(".") - 1) >>> 0) + 2);

        if (fileExtension === 'xls' || fileExtension === 'xlsx') {
            setError(null)
            onUpload(file);
            return;
        }

        setError('Die hochgeladene Datei ist keine Excel-Datei.')
    }
    return (
        <div className="flex-col w-full">
            <label htmlFor="uploadFile1"
                   className={cn("p-2 bg-white text-black text-base rounded w-full h-52 flex flex-col items-center justify-center cursor-pointer border-2 mx-auto", (!error && file) ? 'border-green-600' : (error && file) ? 'border-red-600' : 'border-gray-300 border-dashed')}>
                <svg xmlns="http://www.w3.org/2000/svg" className="w-8 mb-2 fill-black" viewBox="0 0 32 32">
                    <path
                        d="M23.75 11.044a7.99 7.99 0 0 0-15.5-.009A8 8 0 0 0 9 27h3a1 1 0 0 0 0-2H9a6 6 0 0 1-.035-12 1.038 1.038 0 0 0 1.1-.854 5.991 5.991 0 0 1 11.862 0A1.08 1.08 0 0 0 23 13a6 6 0 0 1 0 12h-3a1 1 0 0 0 0 2h3a8 8 0 0 0 .75-15.956z"
                        data-original="#000000"/>
                    <path
                        d="M20.293 19.707a1 1 0 0 0 1.414-1.414l-5-5a1 1 0 0 0-1.414 0l-5 5a1 1 0 0 0 1.414 1.414L15 16.414V29a1 1 0 0 0 2 0V16.414z"
                        data-original="#000000"/>
                </svg>
                {(file?.name) ? file.name : 'Datei hochladen' }
                {error && <p className="text-red-600 text-xs">{error}</p>}
                <input type="file" id='uploadFile1' className="hidden" onChange={handleFileChange}/>
                <p className="text-xs text-gray-400 mt-2">Nur Excel-Dateien sind erlaubt.</p>
            </label>
        </div>
    )
}
