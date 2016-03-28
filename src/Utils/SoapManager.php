<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */
namespace Behat\SoapExtension\Utils;

/**
 * Trait SoapManager.
 *
 * @package Behat\SoapExtension\Utils
 */
trait SoapManager
{
    use ArrayManager;

    /**
     * URL of WSDL service to consume.
     *
     * @var string $wsdl
     */
    private $wsdl = '';
    /**
     * Set of options for SOAP request.
     *
     * @var array
     */
    private $options = [];
    /**
     * Response of SOAP method.
     *
     * @var mixed
     */
    private $response;
    /**
     * Last SOAP response.
     *
     * @var string
     */
    private $rawResponse = '';
    /**
     * The URIs of the namespaces.
     *
     * @var string[]
     */
    private $namespaces = [];

    /**
     * Make SOAP call to a function with params.
     *
     * @param string $function
     *   SOAP function name to execute. Use MethodNameIsIgnored if function name is in the XML body.
     * @param array $arguments
     *   Arguments array to pass to soap call function.
     */
    protected function sendRequest($function, array $arguments)
    {
        // These values can be easily overridden inside of configuration file.
        $this->options += [
            // Important for raw response steps.
            'trace' => 1,
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE,
        ];

        $client = new \SoapClient($this->wsdl, $this->options);

        $this->response = $client->__soapCall($function, $arguments);
        $this->rawResponse = $client->__getLastResponse();
    }

    /**
     * Extracts first value matching provided XPATH expression.
     *
     * @param string $query
     *   XPATH expression used to extract value from $this->rawResponse
     *
     * @return \DOMNode|bool
     */
    protected function extractResponseValueMatchingXPATH($query)
    {
        // @todo: Allow users to ignore namespaces via config or steps.
        // @example: $this->rawResponse = str_replace('xmlns=', 'ns=', $this->rawResponse);
        $dom = new \DOMDocument();
        $dom->loadXML($this->rawResponse);
        $xpath = new \DOMXpath($dom);

        foreach ($this->namespaces as $prefix => $uri) {
            $xpath->registerNamespace($prefix, $uri);
        }

        $nodeList = $xpath->query($query);

        return $nodeList->length > 0 ? $nodeList->item(0)->nodeValue : false;
    }

    /**
     * Helper to extract a property value from the response.
     *
     * @param string $property
     *
     * @return mixed
     */
    protected function extractResponseProperty($property)
    {
        return static::arrayValue(static::objectToArray($this->response), explode('][', $property));
    }

    /**
     * @param string $wsdl
     */
    protected function setWSDL($wsdl)
    {
        $this->wsdl = (string) $wsdl;
    }

    /**
     * @param string $option
     * @param mixed $value
     */
    protected function setOption($option, $value)
    {
        $this->options[$option] = defined($value) ? constant($value) : $value;
    }

    /**
     * Reset all request options.
     */
    protected function unsetOptions()
    {
        $this->options = [];
    }

    /**
     * @param string $prefix
     * @param string $uri
     */
    protected function setNamespace($prefix, $uri)
    {
        $this->namespaces[$prefix] = $uri;
    }

    /**
     * Reset all namespaces.
     */
    protected function unsetNamespaces()
    {
        $this->namespaces = [];
    }
}
