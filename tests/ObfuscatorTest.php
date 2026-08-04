<?php

namespace Kudashevs\HtmlObfuscator\Tests;

use Kudashevs\HtmlObfuscator\Obfuscator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ObfuscatorTest extends TestCase
{
    #[Test]
    public function it_can_obfuscate(): void
    {
        $obfuscator = new Obfuscator();

        $obfuscated = $obfuscator->obfuscate('test');

        $this->assertIsString($obfuscated);
        $this->assertNotEmpty($obfuscated);
        $this->assertNotSame('test', $obfuscated);
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
}
