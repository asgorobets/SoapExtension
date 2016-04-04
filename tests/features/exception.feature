Feature: Test exceptions

  Background:
    Given I am working with SOAP service WSDL "http://wsf.cdyne.com/WeatherWS/Weather.asmx?WSDL"
    # Wrong method used.
    And I call SOAP function "GetCityForecastByZIP1" with params list:
      | ZIP | 94040 |

  @pass
  Scenario: Catch any exception
    And expect SOAP exception

  @fail
  Scenario: Do not expect an exception
    Then I should see SOAP response property "GetCityForecastByZIPResult][City" equals to "Mountain View"

  @pass
  Scenario: Test with valid exit code
    # Execution of a wrong method exits with zero response code.
    And expect SOAP exception with code "0"

  @fail
  Scenario: Test with wrong exit code
    # Execution of a wrong method exits with zero response code.
    And expect SOAP exception with code "1"

  @pass
  Scenario: Test with valid message
    And expect SOAP exception with message "not a valid method for this service"

  @fail
  Scenario: Test with wrong message
    And expect SOAP exception with message "What do you expect to have here?"

  # OR condition.
  @pass
  Scenario: Test with wrong exit code and valid message (OR condition)
    And expect SOAP exception with code "30" or with message "not a valid method for this service"

  @pass
  Scenario: Test with valid exit code and wrong message (OR condition)
    And expect SOAP exception with code "0" or with message "What do you expect to have here?"

  @pass
  Scenario: Test with valid exit code and valid message (OR condition)
    And expect SOAP exception with code "0" or with message "not a valid method for this service"

  # AND condition.
  @fail
  Scenario: Test with wrong exit code and valid message (AND condition)
    And expect SOAP exception with code "30" and with message "not a valid method for this service"

  @fail
  Scenario: Test with valid exit code and wrong message (AND condition)
    And expect SOAP exception with code "0" and with message "What do you expect to have here?"

  @fail
  Scenario: Test with wrong exit code and wrong message (AND condition)
    And expect SOAP exception with code "30" and with message "What do you expect to have here?"
