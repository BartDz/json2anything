<?php

namespace App\Tests\Converter;

use App\Converter\PhpArrayConverter;
use PHPUnit\Framework\TestCase;

class PhpArrayConverterTest extends TestCase
{
    private PhpArrayConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new PhpArrayConverter();
    }

    public function testConvertsSimpleObject(): void
    {
        $result = $this->converter->convert(['key' => 'value']);
        $this->assertStringContainsString("'key' => 'value'", $result);
    }

    public function testConvertsNestedArray(): void
    {
        $result = $this->converter->convert(['nested' => ['a' => 1]]);
        $this->assertStringContainsString("'nested' =>", $result);
        $this->assertStringContainsString("'a' => 1", $result);
    }

    public function testHandlesNull(): void
    {
        $result = $this->converter->convert(['val' => null]);
        $this->assertStringContainsString('null', $result);
    }

    public function testHandlesBooleans(): void
    {
        $result = $this->converter->convert(['flag' => true]);
        $this->assertStringContainsString('true', $result);
    }
}
