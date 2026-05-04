<?php

namespace App\Converter;

interface ConverterInterface
{
    public function convert(array $data): string;
}
