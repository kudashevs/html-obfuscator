<?php

namespace Kudashevs\HtmlObfuscator\Tests;

use Kudashevs\HtmlObfuscator\Obfuscator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ObfuscatorTest extends TestCase
{
    #[Test]
    public function it_throws_exception_on_a_wrong_dynamic_call(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Instance');

        (new Obfuscator())->wrong();
    }

    #[Test]
    public function it_throws_exception_on_a_wrong_static_call(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Static');

        Obfuscator::wrong();
    }

    #[Test]
    public function it_can_handle_empty(): void
    {
        $obfuscator = new Obfuscator();

        $obfuscated = $obfuscator->obfuscate('');

        $this->assertIsString($obfuscated);
        $this->assertEmpty($obfuscated);
    }

    #[Test]
    public function it_can_obfuscate(): void
    {
        $obfuscator = new Obfuscator();

        $obfuscated = $obfuscator->obfuscate(str_repeat('test', 10));

        $this->assertIsString($obfuscated);
        $this->assertNotEmpty($obfuscated);
        $this->assertNotSame(str_repeat('test', 10), $obfuscated);
    }

    #[Test]
    public function it_can_obfuscate_with_existing_strategy(): void
    {
        $obfuscator = new Obfuscator(['preferred_strategy' => 'hash']);

        $this->assertSame('&#116;&#101;&#115;&#116;', $obfuscator->obfuscate('test'));
    }

    #[Test]
    #[DataProvider('provideStrategies')]
    public function it_can_use_a_preferred_strategy(string $strategy, string $expected): void
    {
        $obfuscator = new Obfuscator(['preferred_strategy' => $strategy]);

        $this->assertSame($expected, $obfuscator->obfuscate('test'));
    }

    public static function provideStrategies(): array
    {
        return [
            'default' => ['default', 'test'],
            'hash'    => ['hash', '&#116;&#101;&#115;&#116;'],
            'strpad'  => ['strpad', '&#0116;&#0101;&#0115;&#0116;'],
            'xdechex' => ['xdechex', '&#x74;&#x65;&#x73;&#x74;'],
            'Xdechex' => ['Xdechex', '&#X74;&#X65;&#X73;&#X74;'],
            'sdechex' => ['sdechex', '&#x0074;&#x0065;&#x0073;&#x0074;'],
        ];
    }

    #[Test]
    public function it_can_use_default_strategy(): void
    {
        $obfuscator = new Obfuscator([
            'preferred_strategy' => 'default',
        ]);

        $obfuscated = $obfuscator->obfuscate('test');

        $this->assertSame('test', $obfuscated);
    }

    #[Test]
    public function it_can_use_random_strategies(): void
    {
        $obfuscator = new Obfuscator();

        $obfuscated = $obfuscator->obfuscate('test');

        $this->assertNotSame('test', $obfuscated);
    }

    #[Test]
    public function it_preserves_non_ascii_symbols(): void
    {
        $obfuscated = Obfuscator::obfuscate('let\'s 🚀');

        $this->assertIsString($obfuscated);
        $this->assertNotEmpty($obfuscated);
        $this->assertTrue(str_ends_with($obfuscated, '🚀'));
    }
}
