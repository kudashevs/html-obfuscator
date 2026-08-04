# HTML obfuscator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kudashevs/html-obfuscator.svg)](https://packagist.org/packages/kudashevs/html-obfuscator)
[![Run Tests](https://github.com/kudashevs/html-obfuscator/actions/workflows/run-tests.yml/badge.svg)](https://github.com/kudashevs/html-obfuscator/actions/workflows/run-tests.yml)
[![License MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE.md)

A PHP implementation of an HTML obfuscator. The obfuscator encodes a provided text into different numeric codes to mask
content from simple automated scrapers while the content keeps rendering normally in a browser.


## Installation

You can install the package via composer:
```bash
composer require kudashevs/html-obfuscator
```


## Usage

The obfuscator can be used both statically and dynamically. The only limitation of the dynamic approach is that it is not
possible to provide custom settings during the instantiation. However, in the vast majority of use cases, these settings
are not needed. I would recommend using the dynamic approach because it is generally more flexible.

```php
use Kudashevs\HtmlObfuscator\Obfuscator;

$text = 'the answer for 3 + 2 is';

// static invocation
$obfuscated = Obfuscator::obfuscate($text);

// dynamic invocation
$obfuscator = new Obfuscator();
$obfuscated = $obfuscator->obfuscate($text);

print_r($obfuscated);
// both cases will result into
t&#x0068;&#X65;&#0032;&#x61;&#x6e;&#115;w&#0101;&#X72;&#x0020;fo&#x0072;&#x0020;&#0051; +&#x20;2&#x0020;i&#x0073;
```


## Testing

```bash
composer test
```


## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.
 **Note:** Please make sure to update tests as appropriate.


## License

The MIT License (MIT). Please see the [License file](LICENSE.md) for more information.
