# Behat SoapExtension

SoapExtension is a Behat extension designed to test various SOAP APIs using Behat framework and it's powerful tooling. Soap Extension currently supports only PHP's native SoapClient as transport and there are no plans to add other clients support at this time. Full project roadmap will come later.

[![Latest Stable Version](https://poser.pugx.org/behat/soap-extension/v/stable)](https://packagist.org/packages/behat/soap-extension)
[![License](https://poser.pugx.org/behat/soap-extension/license)](https://packagist.org/packages/behat/soap-extension)
[![Build Status](https://img.shields.io/travis/asgorobets/SoapExtension/master.svg?style=flat)](https://travis-ci.org/asgorobets/SoapExtension)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/asgorobets/SoapExtension.svg?style=flat)](https://scrutinizer-ci.com/g/asgorobets/SoapExtension/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/asgorobets/SoapExtension.svg?style=flat)](https://scrutinizer-ci.com/g/asgorobets/SoapExtension)
[![Total Downloads](https://poser.pugx.org/behat/soap-extension/downloads)](https://packagist.org/packages/behat/soap-extension)

## Installation

- `curl -sS https://getcomposer.org/installer | php`
- `vim composer.json`
```json
{
  "require": {
    "behat/soap-extension": "dev-master"
  },
  "config": {
    "bin-dir": "bin"
  }
}
```
- `composer install`
- Enable `SoapExtension` in [behat.yml](docs/behat.yml#L6-L10)

## Documentation

- [Example feature](docs/features/weather_ws.feature)

[Docs coming soon...](docs/) In the meantime, run `bin/behat -dl` for a list of available steps.

## Testing

```shell
./vendor/bin/phpunit
cd tests/ && ../vendor/bin/behat
```

## Contributions

Feel free to provide feedback in issue queue and contributions are much welcome.

## Authors

- [Alexei Gorobets (asgorobets)](https://github.com/asgorobets)
- [Sergii Bondarenko (BR0kEN-)](https://github.com/BR0kEN-)

## Supporting organizations

Thanks to [FFW Agency](http://www.ffwagency.com/) for supporting this contribution.
