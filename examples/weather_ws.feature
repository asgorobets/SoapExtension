Feature: Simple test example
  As a SOAP Extension developer
  I want to show some cool features you can do with it
  So that you can use the extension effectively on your projects

  Scenario: WeatherWS SOAP test
    Given I am working with SOAP service WSDL "http://wsf.cdyne.com/WeatherWS/Weather.asmx?WSDL"
    And I call SOAP function "GetCityForecastByZIP" with params list:
    | ZIP | 94040 |
    Then I should see SOAP response property "GetCityForecastByZIPResult][City" equals to "Mountain View"
    When I register the following XPATH namespaces:
    | ws | http://ws.cdyne.com/WeatherWS/ |
    Then I should see that SOAP Response matches XPATH "//ws:City"
    Given I am working with SOAP element matching XPATH "//ws:City"
    Then saved SOAP value doesn't match "(San Francisco|Santa Clara)"


