<?php

namespace App\Converter;

class PhpArrayConverter
{
    public function convert(array $data): string
    {
        return "<?php\n\nreturn " . $this->format($data, 0) . ';';
    }

    private function format(array $data, int $depth): string
    {
        $pad  = str_repeat('    ', $depth);
        $ipad = str_repeat('    ', $depth + 1);
        $list = array_is_list($data);
        $items = [];

        foreach ($data as $key => $value) {
            $v = match (true) {
                is_array($value)  => $this->format($value, $depth + 1),
                is_string($value) => "'" . str_replace(["\\", "'"], ["\\\\", "\\'"], $value) . "'",
                is_null($value)   => 'null',
                is_bool($value)   => $value ? 'true' : 'false',
                default           => (string) $value,
            };

            $items[] = $list
                ? "$ipad$v"
                : "$ipad'" . str_replace(["\\", "'"], ["\\\\", "\\'"], (string) $key) . "' => $v";
        }

        return "[\n" . implode(",\n", $items) . ",\n$pad]";
    }
}
