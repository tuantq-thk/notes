<?php

namespace Helper\Import\Csv;

abstract class Base
{
    protected $toEncoding = 'UTF-8';
    protected $fromEncoding = 'SJIS-win';

    /**
     * Get Data From CSV 
     * @param string $file_path
     * @param array $config
     * @param array $options
     * @return array
     */
    public function getDataImport($file_path, $config = [], $options = [])
    {
        
        // if (!file_exists($file_path) || !is_readable($file_path)) {
        //     throw new \Exception("File not exists: $file_path");
        // }

        $delimiter = $options['delimiter'] ?? ',';
        $enclosure = $options['enclosure'] ?? '"';
        $escape = $options['escape'] ?? '\\';

        $data = [];
        $errors = [];

        if (($handle = fopen($file_path, 'r')) !== false) {
            $headers = fgetcsv($handle, 0, $delimiter, $enclosure, $escape);
            if ($headers === false) {
                throw new \Exception("Cannot read header of CSV.");
            }

            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);

            $headers = array_map(function($header) {
                return mb_convert_encoding($header, $this->toEncoding, $this->fromEncoding);
            }, $headers);

            $headerIndexes = [];
            foreach ($config as $key => $expectedColumn) {
                $index = array_search(trim($expectedColumn), array_map('trim', $headers));
                if ($index === false) {
                    throw new \Exception("Column '$expectedColumn' not exists.");
                }
                $headerIndexes[$key] = $index;
            }

            $rowNumber = 1;
            while (($row = fgetcsv($handle, 0, $delimiter, $enclosure, $escape)) !== false) {
                $rowNumber++;

                $row = array_map(function($col) {
                    return mb_convert_encoding($col, $this->toEncoding, $this->fromEncoding);
                }, $row);

                $rowData = [];
                foreach ($config as $key => $expectedColumn) {
                    $index = $headerIndexes[$key];
                    $rowData[$key] = isset($row[$index]) ? trim($row[$index]) : null;
                }

                // $validationErrors = $this->validateRow($rowData, $rowNumber);
                // if (!empty($validationErrors)) {
                //     $errors[$rowNumber] = $validationErrors;
                // } else {
                    $data[] = $rowData;
                // }
            }
            fclose($handle);
        }

        return ['data' => $data, 'errors' => $errors];
    }

    abstract protected function validateRow($row, $rowNumber): array;
}
