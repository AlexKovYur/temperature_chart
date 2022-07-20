<?php

class CsvParser
{
    public function getData(string $file)
    {
        $row = 0;
        $inserts = [];

        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $file)
            || mime_content_type($_SERVER['DOCUMENT_ROOT'] . '/' . $file) !== 'text/plain') {
            return $inserts;
        }

        if (($handle = fopen($file, 'rb')) !== false) {
            while (($data = fgetcsv($handle, 1500, ';')) !== false) {
                $row++;
                if ($row >= 8 && !empty($data[1])) {
                    $inserts[] = [
                        'date' => date('Y-m-d H:i:s', strtotime($data[0])),
                        'value' => (float)$data[1],
                    ];
                }
            }
            fclose($handle);
        }

        return $inserts;
    }
}