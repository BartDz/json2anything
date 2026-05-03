<?php

namespace App\Converter;

class SqlConverter
{
    public function convert(array $data, string $table = 'items'): string
    {
        $rows = (array_is_list($data) && isset($data[0]) && is_array($data[0]))
            ? $data
            : [$data];

        if (empty($rows[0])) {
            return '';
        }

        $columns    = array_keys($rows[0]);
        $columnList = implode(', ', array_map(fn($c) => '`' . str_replace('`', '``', $c) . '`', $columns));

        $valueRows = array_map(function (array $row) use ($columns) {
            $values = array_map(function (string $col) use ($row) {
                $val = $row[$col] ?? null;
                return match (true) {
                    is_null($val)                      => 'NULL',
                    is_bool($val)                      => $val ? '1' : '0',
                    is_int($val) || is_float($val)     => (string) $val,
                    is_array($val)                     => "'" . str_replace(["\\", "'"], ["\\\\", "''"], json_encode($val)) . "'",
                    default                            => "'" . str_replace(["\\", "'"], ["\\\\", "''"], (string) $val) . "'",
                };
            }, $columns);
            return '(' . implode(', ', $values) . ')';
        }, $rows);

        return "INSERT INTO `$table` ($columnList)\nVALUES\n" . implode(",\n", $valueRows) . ';';
    }
}
