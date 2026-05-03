<?php

namespace App\Converter;

class YamlConverter
{
    public function convert(array $data): string
    {
        return yaml_emit($data, YAML_UTF8_ENCODING);
    }
}
