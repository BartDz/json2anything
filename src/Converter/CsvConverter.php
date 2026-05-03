<?php

namespace App\Converter;

class CsvConverter
{
    public function convert(array $data): string
    {
        $rows = (array_is_list($data) && isset($data[0]) && is_array($data[0]))
            ? $data
            : [$data];

        if (empty($rows[0])) {
            return '';
        }

        $headers = array_keys($rows[0]);
        $buf     = fopen('php://temp', 'r+');

        fputcsv($buf, $headers);

        foreach ($rows as $row) {
            $values = array_map(function (string $col) use ($row) {
                $val = $row[$col] ?? '';
                return is_array($val) ? json_encode($val) : (string) $val;
            }, $headers);
            fputcsv($buf, $values);
        }

        rewind($buf);
        $csv = stream_get_contents($buf);
        fclose($buf);

        return $csv;
    }
}
