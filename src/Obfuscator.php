<?php

declare(strict_types=1);

namespace Kudashevs\HtmlObfuscator;

class Obfuscator
{
    /** @var array<string, string> */
    private array $settings;

    /**
     * @param array<string, string> $settings
     */
    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
    }

    public function obfuscate(string $input): string
    {
        $result = '';

        foreach (mb_str_split($input) as $char) {
            $charCode = mb_ord($char);

            if ($charCode > 127) {
                $result .= $char;
                continue;
            }

            $result .= $this->resolveStrategy()($charCode);
        }

        return $result;
    }

    protected function resolveStrategy(): \Closure
    {
        $strategies = $this->strategies();

        if ($preferred = $this->hasPreferredStrategy($strategies)) {
            return $preferred;
        }

        $randomKey = array_rand($strategies);

        return $strategies[$randomKey];
    }

    /**
     * @param array<string, \Closure> $strategies
     * @return \Closure|null
     */
    private function hasPreferredStrategy(array $strategies): ?\Closure
    {
        if (
            !is_null($this->settings['preferred_strategy'])
            && array_key_exists($this->settings['preferred_strategy'], $strategies)
        ) {
            return $strategies[$this->settings['preferred_strategy']];
        }

        return null;
    }

    /**
     * @return array<string, \Closure>
     */
    private function strategies(): array
    {
        return [
            'default' => fn(int $code) => chr($code),
            'hash' => fn(int $code) => "&#{$code};",
            'strpad' => fn(int $code) => '&#' . str_pad((string)$code, 4, '0', STR_PAD_LEFT) . ';',
            'xdechex' => fn(int $code) => '&#x' . dechex($code) . ';',
            'Xdechex' => fn(int $code) => '&#X' . strtoupper(dechex($code)) . ';',
            'sdechex' => fn(int $code) => '&#x' . str_pad(dechex($code), 4, '0', STR_PAD_LEFT) . ';',
        ];
    }
}
