Feature: Simple test example
  As a SOAP Extension user
  I want to be able to use my own extended SOAPContext
  So that I can add additional steps that work in conjunction with the basic SOAPContext functionality

  Scenario: WeatherWS SOAP and custom properties test with predefined A and B
    Given I am working with SOAP service WSDL "http://wsf.cdyne.com/WeatherWS/Weather.asmx?WSDL"
    And I call SOAP function "GetCityForecastByZIP" with params list:
      | ZIP | 94040 |
    And I want to check that "Mountain View" equals to A and not B
