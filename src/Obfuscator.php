<?php

declare(strict_types=1);

namespace Kudashevs\HtmlObfuscator;

class Obfuscator
{
    public const DANGEROUS_CHARACTERS = "<>&\"'";

    protected ?\Closure $preferredStrategy;

    /**
     * @param array<string, string> $settings
     */
    public function __construct(array $settings = [])
    {
        $this->init($settings);
    }

    /**
     * @param array<string, string> $settings
     */
    protected function init(array $settings): void
    {
        $this->preferredStrategy = $this->retrievePreferredStrategy($settings);
    }

    /**
     * @param string $name
     * @param array<array-key, string> $arguments
     * @return string
     */
    public static function __callStatic(string $name, array $arguments): string
    {
        if ($name === 'obfuscate') {
            return (new self())->obfuscate($arguments[0]);
        }

        throw new \BadMethodCallException(
            sprintf('Static method %s does not exist.', $name)
        );
    }

    /**
     * @param string $name
     * @param array<array-key, string> $arguments
     * @return string
     */
    public function __call(string $name, array $arguments): string
    {
        if ($name === 'obfuscate') {
            return $this->obfuscate($arguments[0]);
        }

        throw new \BadMethodCallException(
            sprintf('Instance method %s does not exist.', $name)
        );
    }

    private function obfuscate(string $input): string
    {
        $result = '';

        foreach (mb_str_split($input) as $char) {
            $charCode = mb_ord($char);

            if ($charCode > 127) {
                $result .= $char;
                continue;
            }

            $result .= $this->resolveStrategy($char)($charCode);
        }

        return $result;
    }

    protected function resolveStrategy(string $char): \Closure
    {
        if (str_contains(self::DANGEROUS_CHARACTERS, $char)) {
            return $this->pickRandomStrategy(
                $this->safeStrategies()
            );
        }

        return ($this->preferredStrategy)
            ? $this->preferredStrategy
            : $this->pickRandomStrategy(
                $this->strategies()
            );
    }

    /**
     * @param array<string, \Closure> $strategies
     * @return \Closure
     */
    protected function pickRandomStrategy(array $strategies): \Closure
    {
        $randomKey = array_rand($strategies);

        return $strategies[$randomKey];
    }

    /**
     * @param array<string, string> $settings
     * @return \Closure|null
     */
    private function retrievePreferredStrategy(array $settings): ?\Closure
    {
        $strategies = $this->strategies();

        if (
            !empty($settings['preferred_strategy'])
            && array_key_exists($settings['preferred_strategy'], $strategies)
        ) {
            return $strategies[$settings['preferred_strategy']];
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

    /**
     * @return array<string, \Closure>
     */
    private function safeStrategies(): array
    {
        $unsafeStrategies = ['default'];

        return array_diff_key($this->strategies(), array_flip($unsafeStrategies));
    }
}
