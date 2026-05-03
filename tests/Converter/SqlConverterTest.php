<?php

namespace App\Tests\Converter;

use App\Converter\SqlConverter;
use PHPUnit\Framework\TestCase;

class SqlConverterTest extends TestCase
{
    private SqlConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new SqlConverter();
    }

    public function testConvertsSingleObject(): void
    {
        $result = $this->converter->convert(['name' => 'Alice', 'age' => 30]);
        $this->assertStringContainsString('INSERT INTO', $result);
        $this->assertStringContainsString('`name`', $result);
        $this->assertStringContainsString("'Alice'", $result);
        $this->assertStringContainsString('30', $result);
    }

    public function testConvertsArrayOfObjects(): void
    {
        $result = $this->converter->convert([
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob'],
        ]);
        $this->assertStringContainsString('VALUES', $result);
        $this->assertStringContainsString("'Alice'", $result);
        $this->assertStringContainsString("'Bob'", $result);
    }

    public function testSerializesNestedObjectAsJson(): void
    {
        $result = $this->converter->convert(['meta' => ['role' => 'admin']]);
        $this->assertStringContainsString('"role"', $result);
    }
}
