<?php

namespace App\Tests\Converter;

use App\Converter\CsvConverter;
use PHPUnit\Framework\TestCase;

class CsvConverterTest extends TestCase
{
    private CsvConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new CsvConverter();
    }

    public function testConvertsSingleRow(): void
    {
        $result = $this->converter->convert(['name' => 'Alice', 'age' => 30]);
        $lines  = explode("\n", trim($result));
        $this->assertCount(2, $lines);
        $this->assertStringContainsString('name', $lines[0]);
        $this->assertStringContainsString('Alice', $lines[1]);
    }

    public function testConvertsMultipleRows(): void
    {
        $result = $this->converter->convert([
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob'],
        ]);
        $lines = explode("\n", trim($result));
        $this->assertCount(3, $lines);
    }

    public function testSerializesNestedObjectAsJson(): void
    {
        $result = $this->converter->convert(['meta' => ['role' => 'admin']]);
        $this->assertStringContainsString('role', $result);
    }
}
