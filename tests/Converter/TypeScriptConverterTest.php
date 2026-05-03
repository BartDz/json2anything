<?php

namespace App\Tests\Converter;

use App\Converter\TypeScriptConverter;
use PHPUnit\Framework\TestCase;

class TypeScriptConverterTest extends TestCase
{
    private TypeScriptConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new TypeScriptConverter();
    }

    public function testConvertsSimpleObject(): void
    {
        $result = $this->converter->convert(['name' => 'Alice', 'age' => 30]);
        $this->assertStringContainsString('interface Root', $result);
        $this->assertStringContainsString('name: string', $result);
        $this->assertStringContainsString('age: number', $result);
    }

    public function testConvertsNestedObject(): void
    {
        $result = $this->converter->convert(['user' => ['id' => 1]]);
        $this->assertStringContainsString('interface User', $result);
        $this->assertStringContainsString('user: User', $result);
    }

    public function testConvertsArrayOfStrings(): void
    {
        $result = $this->converter->convert(['tags' => ['a', 'b']]);
        $this->assertStringContainsString('tags: string[]', $result);
    }

    public function testHandlesNull(): void
    {
        $result = $this->converter->convert(['val' => null]);
        $this->assertStringContainsString('val: null', $result);
    }
}
