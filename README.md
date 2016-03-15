# Behat SoapExtension

SoapExtension is a Behat extension designed to test various SOAP APIs using Behat framework and it's powerful tooling. Soap Extension currently supports only PHP's native SoapClient as transport and there are no plans to add other clients support at this time. Full project roadmap will come later.

## Contributions

Feel free to provide feedback in issue queue and contributions are much welcome.

## Installation

- `curl -sS https://getcomposer.org/installer | php`
- `vim composer.json`
```json
{
  "require": {
    "asgorobets/soap-extension": "dev-master"
  },
  "config": {
    "bin-dir": "bin"
  }
}
```
- Run bin/behat --init and/or configure `behat.yml` to include SoapContext and enable SoapExtension:
```
default:
  suites:
    default:
      contexts:
        - FeatureContext: ~
        - Behat\SoapExtension\Context\SoapContext: ~
  extensions:
    Behat\SoapExtension: ~
```

## Documentation

- [Example feature](examples/weather_ws.feature)

Docs coming soon... in the meantime, run bin/behat -dl for a list of available steps

## Authors

- [Alexei Gorobets (asgorobets)](https://github.com/asgorobets)

## Supporting organizations

Thanks to [FFW Agency](http://www.ffwagency.com/) for supporting this contribution
