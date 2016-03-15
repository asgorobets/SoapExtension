<?php

/*
 * This file is part of the Behat SoapExtension.
 * (c) Alexei Gorobet <asgorobets@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\SoapExtension\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use SoapClient;
use Behat\Gherkin\Node\TableNode;
use PHPUnit_Framework_Assert as Assertions;
use Symfony\Component\Yaml\Yaml;

/**
 * Provides web API description definitions.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SoapContext implements Context
{
    /**
     * @var string $wsdl
     *   WSDL url of the service to consume.
     */
    private $wsdl;

    private $client;

    private $options = array();

    private $arguments = array();

    private $response;

    private $rawResponse;

    private $namespaces = array();

    private $savedValue;

    /**
     * Sets the WSDL for the next SOAP request.
     *
     * @param string $wsdl
     *   Publicly accessible URL to wsdl
     *
     * @Given I am working with SOAP service WSDL :wsdl
     */
    public function iAmWorkingWithSoapServiceWSDL($wsdl)
    {
        $this->wsdl = $wsdl;
    }

    /**
     * Sets the WSDL for the next SOAP request to NULL.
     *
     * @Given I am working with SOAP service in non-WSDL mode
     */
    public function iAmWorkingWithSoapServiceNoWSDL()
    {
        $this->wsdl = NULL;
    }

    /**
     * @Given I am working with SOAP service with options list:
     */
    public function iAmWorkingWithSoapServiceWithOptions(TableNode $options)
    {
        $this->options = array();
        $options = $options->getRowsHash();
        // Attempt to interpolate constants.
        $options = array_map(function ($option) {
            return defined($option) ? constant($option) : $option;
        }, $options);

        if (!empty($options) && is_array($options)) {
            $this->options = $options;
        }
    }

    /**
     * @Given I am working with SOAP service with options as YAML:
     */
    public function iAmWorkingWithSoapServiceWithOptionsYaml(PyStringNode $options)
    {
        $this->options = array();
        $options = YAML::parse($options->getRaw());

        if (!empty($options) && is_array($options)) {
            $this->options = $options;
        }
    }

    /**
     * Send SOAP request with params list.
     *
     * @Given I call SOAP function :function with params list:
     */
    public function iSendRequestWithParams($function, TableNode $params)
    {
        $this->arguments = array($params->getRowsHash());
        $this->sendRequest($function, $this->arguments);
    }

    /**
     * Send SOAP request with raw body.
     *
     * @Given I call SOAP with raw body:
     */
    public function iSendRequestBody(PyStringNode $body)
    {
        // Tell Soap we want to send the body as XML, if not otherwise specified.
        $this->options += array(
          'use' => SOAP_LITERAL,
          'style' => SOAP_DOCUMENT,
        );

        $this->arguments = array(new \SoapVar($body->getRaw(), XSD_ANYXML));
        $this->sendRequest('MethodNameIsIgnored', $this->arguments);
    }

    /**
     * Make SOAP call to a function with params.
     *
     * @param string $function
     *   SOAP function name to execute. Use MethodNameIsIgnored if function name is in the XML body.
     * @param mixed $arguments
     *   Arguments array to pass to soap call function.
     */
    private function sendRequest($function, $arguments)
    {
        // TODO: Get default options from extension config.
        $options = $this->options += array(
          'trace' => 1, // Important for raw response steps.
          'exceptions' => TRUE,
          'cache_wsdl' => WSDL_CACHE_NONE,
        );

        $this->client = new SoapClient($this->wsdl, $this->options);
        $response = $this->client->__soapCall($function, $this->arguments);
        $this->setResponse($response);
        $this->setRawResponse($this->client->__getLastResponse());
    }

    /**
     * @Given I register the following XPATH namespaces:
     */
    public function iRegisterXpathNamespaces(TableNode $namespaces) {
        $this->setNamespaces($namespaces->getRowsHash());
    }

    /**
     * @Given I should see SOAP response property :property equals to :text
     */
    public function iShouldSeeSoapResponsePropertyEquals($text, $property)
    {
        $value = $this->extractResponseProperty($property);
        Assertions::assertEquals($text, $value);
    }

    /**
     * @Given I should see SOAP response property :property is not :text
     */
    public function iShouldSeeSoapResponsePropertyNotEquals($text, $property)
    {
        $value = $this->extractResponseProperty($property);
        Assertions::assertNotEquals($text, $value);
    }

    /**
     * @Given I should see SOAP response property :property contains :text
     */
    public function iShouldSeeSoapResponsePropertyContains($text, $property)
    {
        $value = $this->extractResponseProperty($property);
        Assertions::assertContains($text, $value);
    }

    /**
     * @Given I should see SOAP response property :property doesn't contain :text
     */
    public function iShouldSeeSoapResponsePropertyNotContains($text, $property)
    {
        $value = $this->extractResponseProperty($property);
        Assertions::assertNotContains($text, $value);
    }

    /**
     * @Then I should see SOAP response property :property matching pattern :pattern
     */
    public function iShouldSeeSoapResponsePropertyMatches($pattern, $property)
    {
        $value = $this->extractResponseProperty($property);
        Assertions::assertRegExp($pattern, $value);
    }

    /**
     * @Then I should see that SOAP Response matches XPATH :xpath
     */
    public function iShouldSeeThatSOAPResponseMatchesXpath($xpath) {
        Assertions::assertTrue($this->extractResponseValueMatchingXPATH($xpath) !== FALSE, "Couldn't find node matching provided XPATH: ");
    }

    /**
     * @Given I am working with SOAP response property :property
     */
    public function iWorkWithResponseProperty($property) {
        $this->savedValue = $this->extractResponseProperty($property);
    }

    /**
     * @Given I am working with SOAP element matching XPATH :xpath
     */
    public function iWorkWithElementTextMatchingXPATH($xpath) {
        $this->savedValue = $this->extractResponseValueMatchingXPATH($xpath);
    }

    /**
     * @Then saved SOAP value equals to :text
     */
    public function savedValueEquals($text) {
        Assertions::assertEquals($text, $this->savedValue);
    }

    /**
     * @Then saved SOAP value is not equal to :text
     */
    public function savedValueNotEquals($text) {
        Assertions::assertNotEquals($text, $this->savedValue);
    }

    /**
     * @Then saved SOAP value contains :text
     */
    public function savedValueContains($text) {
        Assertions::assertContains($text, $this->savedValue);
    }

    /**
     * @Then saved SOAP value doesn't contain :text
     */
    public function savedValueNotContains($text) {
        Assertions::assertNotContains($text, $this->savedValue);
    }

    /**
     * @Then saved SOAP value matches :pattern
     */
    public function savedValueMatchesRegExp($pattern) {
        Assertions::assertRegExp($pattern, $this->savedValue);
    }

    /**
     * @Then saved SOAP value doesn't match :pattern
     */
    public function savedValueNotMatchesRegExp($pattern) {
        Assertions::assertNotRegExp($pattern, $this->savedValue);
    }

    /**
     * Extracts first value matching provided XPATH expression.
     * @param $xpath
     *   XPATH expression used to extract value from $this->rawResponse
     * @return \DOMNode|false
     *   Return DOMNode instanse of a matched element, or FALSE if none was found.
     *
     */
    private function extractResponseValueMatchingXPATH($xpath) {
        $clean_xml = $this->getRawResponse();

        // @todo: Allow users to ignore namespaces via config or steps.
        // @example: $clean_xml = str_replace('xmlns=', 'ns=', $clean_xml);

        $dom = new \DOMDocument();
        $dom->loadXML($clean_xml);
        $dom_xpath = new \DOMXpath($dom);

        // @todo Allow configurable namespaces from extension config.
        foreach ($this->getNamespaces() as $prefix => $namespaceURI) {
            $dom_xpath->registerNamespace($prefix, $namespaceURI);
        }

        $node_list = $dom_xpath->query($xpath);

        if ($node_list->length > 0) {
            return $dom_xpath->query($xpath)->item(0)->nodeValue;
        }
        else {
            return FALSE;
        }
    }

    /**
     * Helper to extract a property value from the response.
     * @param $property
     * @return mixed
     */
    private function extractResponseProperty($property) {
        $response = $this->getResponse();
        $response = self::object_to_array($response);
        $parents = explode('][', $property);
        return self::drupal_array_get_nested_value($response, $parents);
    }

    /**
     * Helper function to convert mixed array and objects structure to arrays.
     *
     * @param $obj
     * @return array
     */
    public static function object_to_array($obj) {
        if(is_object($obj)) $obj = (array) $obj;
        if(is_array($obj)) {
            $new = array();
            foreach($obj as $key => $val) {
                $new[$key] = self::object_to_array($val);
            }
        }
        else $new = $obj;
        return $new;
    }

    /**
     * Retrieves a value from a nested array with variable depth.
     *
     * This helper function should be used when the depth of the array element being
     * retrieved may vary (that is, the number of parent keys is variable).
     *
     * @param $array
     *   The array from which to get the value.
     * @param $parents
     *   An array of parent keys of the value, starting with the outermost key.
     * @param $key_exists
     *   (optional) If given, an already defined variable that is altered by
     *   reference.
     *
     * @return mixed
     *   The requested nested value. Possibly NULL if the value is NULL or not all
     *   nested parent keys exist. $key_exists is altered by reference and is a
     *   Boolean that indicates whether all nested parent keys exist (TRUE) or not
     *   (FALSE). This allows to distinguish between the two possibilities when NULL
     *   is returned.
     */
    public static function &drupal_array_get_nested_value(array &$array, array $parents, &$key_exists = NULL) {
        $ref = &$array;
        foreach ($parents as $parent) {
            if (is_array($ref) && array_key_exists($parent, $ref)) {
                $ref = &$ref[$parent];
            }
            else {
                $key_exists = FALSE;
                $null = NULL;
                return $null;
            }
        }
        $key_exists = TRUE;
        return $ref;
    }

    /**
     * @return string
     */
    public function getWsdl() {
        return $this->wsdl;
    }

    /**
     * @param string $wsdl
     */
    public function setWsdl($wsdl) {
        $this->wsdl = $wsdl;
    }

    /**
     * @return mixed
     */
    public function getClient() {
        return $this->client;
    }

    /**
     * @param mixed $client
     */
    public function setClient(SoapClient $client) {
        $this->client = $client;
    }

    /**
     * @return mixed
     */
    public function getOptions() {
        return $this->options;
    }

    /**
     * @param mixed $options
     */
    public function setOptions($options) {
        $this->options = $options;
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function setOption($key, $value) {
        $this->options[$key] = $value;
    }

    /**
     * @return mixed
     */
    public function getResponse() {
        return $this->response;
    }

    /**
     * @param mixed $response
     */
    public function setResponse($response) {
        $this->response = $response;
    }

    /**
     * @return mixed
     */
    public function getRawResponse() {
        return $this->rawResponse;
    }

    /**
     * @param mixed $rawResponse
     */
    public function setRawResponse($rawResponse) {
        $this->rawResponse = $rawResponse;
    }

    /**
     * @return mixed
     */
    public function getNamespaces() {
        return $this->namespaces;
    }

    /**
     * @param mixed $namespaces
     */
    public function setNamespaces($namespaces) {
        $this->namespaces = $namespaces;
    }

    /**
     * @return mixed
     */
    public function getSavedValue() {
        return $this->savedValue;
    }

    /**
     * @param mixed $savedValue
     */
    public function setSavedValue($savedValue) {
        $this->savedValue = $savedValue;
    }
}
