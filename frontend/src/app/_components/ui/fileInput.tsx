import { useCallback, useEffect, useState } from 'react';
import { cn } from '~/lib/utils';
import Dropzone, { FileRejection, useDropzone } from 'react-dropzone';

interface Props {
  onUpload: (file: File) => void;
  errorMessage?: string | null;
}

interface FileDataType {
  file: File | null;
  error: string | null;
}
const acceptedFiles = {
  'application/vnd.ms-excel': ['.xls'],
  'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': [
    '.xlsx',
  ],
};

const getErrorMessage = (code: string) => {
  switch (code) {
    case 'file-too-large':
      return 'Datei ist zu groß';
    case 'file-too-small':
      return 'Datei ist zu klein';
    case 'too-many-files':
      return 'Zu viele Dateien';
    case 'file-invalid-type':
      return 'Ungültiger Dateityp';
    default:
      return 'Unerwarteter Fehler';
  }
};

export function InputFile({ onUpload, errorMessage = null }: Props) {
  const [fileData, setFileData] = useState<FileDataType>({} as FileDataType);

  useEffect(() => {
    setFileData({ ...fileData, error: errorMessage });
  }, [errorMessage]);

  const { file, error } = fileData;

  const onDrop = (acceptedFiles: File[], fileRejections: FileRejection[]) => {
    acceptedFiles.forEach((file: File) => {
      setFileData({ file: file, error: null });
      onUpload(file);
    });
    fileRejections.forEach((fileRejection) => {
      fileRejection.errors.forEach((error) => {
        setFileData({
          file: fileRejection.file,
          error: getErrorMessage(error.code),
        });
      });
    });
  };

  return (
    <Dropzone
      onDrop={onDrop}
      maxFiles={1}
      accept={acceptedFiles}
      multiple={false}
      autoFocus={true}
    >
      {({ getRootProps, getInputProps }) => (
        <section {...getRootProps({ className: 'dropzone' })}>
          <div className='w-full flex-col'>
            <div
              className={cn(
                'mx-auto flex h-52 w-full cursor-pointer flex-col items-center justify-center rounded border-2 bg-white p-2 text-base text-black',
                !error && file
                  ? 'border-green-600'
                  : error && file
                    ? 'border-red-600'
                    : 'border-dashed border-gray-300'
              )}
            >
              <svg
                xmlns='http://www.w3.org/2000/svg'
                className='mb-2 w-8 fill-black'
                viewBox='0 0 32 32'
                aria-hidden='true'
              >
                <path
                  d='M23.75 11.044a7.99 7.99 0 0 0-15.5-.009A8 8 0 0 0 9 27h3a1 1 0 0 0 0-2H9a6 6 0 0 1-.035-12 1.038 1.038 0 0 0 1.1-.854 5.991 5.991 0 0 1 11.862 0A1.08 1.08 0 0 0 23 13a6 6 0 0 1 0 12h-3a1 1 0 0 0 0 2h3a8 8 0 0 0 .75-15.956z'
                  data-original='#000000'
                />
                <path
                  d='M20.293 19.707a1 1 0 0 0 1.414-1.414l-5-5a1 1 0 0 0-1.414 0l-5 5a1 1 0 0 0 1.414 1.414L15 16.414V29a1 1 0 0 0 2 0V16.414z'
                  data-original='#000000'
                />
              </svg>
              {file?.name ? file.name : 'Datei hochladen'}
              {error && (
                <p
                  className='text-xs text-red-600'
                  aria-describedby='upload-input'
                >
                  {error}
                </p>
              )}
              <input
                {...getInputProps({
                  id: 'upload-input',
                })}
              />
              <p className='mt-2 text-xs text-gray-400'>
                Nur Excel-Dateien sind erlaubt.
              </p>
            </div>
          </div>
        </section>
      )}
    </Dropzone>
  );
}
