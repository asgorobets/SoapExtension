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
          'trace' => 1,
          'exceptions' => TRUE,
          'cache_wsdl' => WSDL_CACHE_NONE,
        );

        $this->client = new SoapClient($this->wsdl, $this->options);
        $response = $this->client->__soapCall($function, $this->arguments);
        $this->setResponse($response);
    }

    /**
     * @Given I should see :text text in SOAP response :property property
     */
    public function iShouldSeeSoapResponsePropertyIncludes($text, $property)
    {
        $response = $this->getResponse();

        // TODO: Extract this as a helper to be reused.
        $response = self::object_to_array($response);
        $parents = explode('][', $property);
        $value = self::drupal_array_get_nested_value($response, $parents);

        Assertions::assertContains($text, $value);
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
}
