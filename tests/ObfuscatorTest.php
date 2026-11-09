<?php

namespace Kudashevs\HtmlObfuscator\Tests;

use Kudashevs\HtmlObfuscator\Obfuscator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

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
        $obfuscated = $this->createObfuscator()
            ->obfuscate('');

        $this->assertIsString($obfuscated);
        $this->assertEmpty($obfuscated);
    }

    #[Test]
    public function it_can_obfuscate(): void
    {
        $input = str_repeat('test', 10);
        $obfuscated = $this->createObfuscator()
            ->obfuscate($input);

        $this->assertIsString($obfuscated);
        $this->assertNotEmpty($obfuscated);
        $this->assertNotSame($input, $obfuscated);
    }

    #[Test]
    public function it_can_obfuscate_with_existing_strategy(): void
    {
        $obfuscated = $this->createObfuscator(['preferred_strategy' => 'hash'])
            ->obfuscate('test');

        $this->assertSame('&#116;&#101;&#115;&#116;', $obfuscated);
    }

    #[Test]
    #[DataProvider('provideStrategies')]
    public function it_can_use_a_preferred_strategy(string $strategy, string $expected): void
    {
        $obfuscated = $this->createObfuscator(['preferred_strategy' => $strategy])
            ->obfuscate('test');

        $this->assertSame($expected, $obfuscated);
    }

    public static function provideStrategies(): array
    {
        return [
            'default' => ['default', 'test'],
            'hash' => ['hash', '&#116;&#101;&#115;&#116;'],
            'strpad' => ['strpad', '&#0116;&#0101;&#0115;&#0116;'],
            'xdechex' => ['xdechex', '&#x74;&#x65;&#x73;&#x74;'],
            'Xdechex' => ['Xdechex', '&#X74;&#X65;&#X73;&#X74;'],
            'sdechex' => ['sdechex', '&#x0074;&#x0065;&#x0073;&#x0074;'],
        ];
    }

    #[Test]
    public function it_can_use_default_strategy(): void
    {
        $obfuscated = $this->createObfuscator(['preferred_strategy' => 'default'])
            ->obfuscate('test');

        $this->assertSame('test', $obfuscated);
    }

    #[Test]
    public function it_can_use_random_strategies(): void
    {
        $obfuscated = $this->createObfuscator()
            ->obfuscate('test');

        $this->assertNotSame('test', $obfuscated);
    }

    #[Test]
    public function it_must_obfuscate_dangerous_symbols(): void
    {
        $dangerousChars = $this->retrieveDangerousCharacters();
        $dangerousInput = implode('', array_map(fn($ch) => str_repeat($ch, 10), $dangerousChars));

        $obfuscated = $this->createObfuscator()
            ->obfuscate($dangerousInput);

        $stripped = preg_replace_callback('/&[^;\s]+;/', static function (array $matches) {
            $decoded = html_entity_decode($matches[0], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            return ($decoded !== $matches[0]) ? '' : $matches[0];
        }, $obfuscated);

        $this->assertSame('', $stripped, "String has dangerous symbols: {$stripped}");
    }

    #[Test]
    public function it_preserves_non_ascii_symbols(): void
    {
        $obfuscated = Obfuscator::obfuscate('let\'s 🚀');

        $this->assertIsString($obfuscated);
        $this->assertNotEmpty($obfuscated);
        $this->assertTrue(str_ends_with($obfuscated, '🚀'));
    }

    private function createObfuscator(array $arguments = []): Obfuscator
    {
        return new Obfuscator($arguments);
    }

    private function retrieveDangerousCharacters(): array
    {
        return str_split(Obfuscator::DANGEROUS_CHARACTERS);
    }
}
