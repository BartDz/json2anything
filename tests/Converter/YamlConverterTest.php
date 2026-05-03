<?php

namespace App\Tests\Converter;

use App\Converter\YamlConverter;
use PHPUnit\Framework\TestCase;

class YamlConverterTest extends TestCase
{
    private YamlConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new YamlConverter();
    }

    public function testConvertsSimpleObject(): void
    {
        $result = $this->converter->convert(['name' => 'Alice', 'age' => 30]);
        $this->assertStringContainsString('name: Alice', $result);
        $this->assertStringContainsString('age: 30', $result);
    }

    public function testConvertsNestedObject(): void
    {
        $result = $this->converter->convert(['user' => ['name' => 'Alice', 'active' => true]]);
        $this->assertStringContainsString('user:', $result);
        $this->assertStringContainsString('name: Alice', $result);
    }

    public function testConvertsArray(): void
    {
        $result = $this->converter->convert(['tags' => ['php', 'yaml']]);
        $this->assertStringContainsString('tags:', $result);
        $this->assertStringContainsString('php', $result);
    }
}
