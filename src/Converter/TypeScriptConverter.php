<?php

namespace App\Converter;

class TypeScriptConverter
{
    private array $interfaces = [];
    private array $interfaceNames = [];

    public function convert(array $data): string
    {
        $this->interfaces = [];
        $this->interfaceNames = [];
        $this->buildInterface($data, 'Root');
        return implode("\n\n", array_reverse($this->interfaces));
    }

    private function buildInterface(array $data, string $name): void
    {
        if (isset($this->interfaceNames[$name])) {
            return;
        }
        $this->interfaceNames[$name] = true;

        $lines = ["interface $name {"];
        foreach ($data as $key => $value) {
            $type = $this->inferType($value, ucfirst((string) $key));
            $safeKey = preg_match('/^[a-zA-Z_$][a-zA-Z0-9_$]*$/', (string) $key)
                ? $key
                : '"' . addslashes((string) $key) . '"';
            $lines[] = "  $safeKey: $type;";
        }
        $lines[] = '}';
        $this->interfaces[] = implode("\n", $lines);
    }

    private function inferType(mixed $value, string $key): string
    {
        return match (true) {
            is_null($value)                             => 'null',
            is_bool($value)                             => 'boolean',
            is_int($value) || is_float($value)          => 'number',
            is_string($value)                           => 'string',
            is_array($value) && array_is_list($value)   => $this->inferArrayType($value, $key),
            is_array($value)                            => $this->buildNested($value, $key),
            default                                     => 'unknown',
        };
    }

    private function buildNested(array $data, string $name): string
    {
        $this->buildInterface($data, $name);
        return $name;
    }

    private function inferArrayType(array $arr, string $key): string
    {
        if (empty($arr)) {
            return 'unknown[]';
        }
        $types = array_values(array_unique(array_map(fn($v) => $this->inferType($v, $key . 'Item'), $arr)));
        $type  = count($types) === 1
            ? $types[0]
            : '(' . implode(' | ', $types) . ')';
        return $type . '[]';
    }
}
