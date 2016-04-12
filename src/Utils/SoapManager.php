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
     * @var string|null $wsdl
     */
    private $wsdl;
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
    private $response = [];
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
     * Latest exception thrown out during SOAP call.
     *
     * @var null|\SoapFault
     */
    private $exception;

    /**
     * Make SOAP call to a function with params.
     *
     * @link http://php.net/manual/en/soapclient.getlastrequest.php#example-5896
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

        try {
            $client = new \SoapClient($this->wsdl, $this->options);

            $this->response = $client->__soapCall($function, $arguments);
            $this->rawResponse = $client->__getLastResponse();
        } catch (\SoapFault $e) {
            $this->exception = $e;
        }
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
     * @return null|\SoapFault
     */
    protected function getException()
    {
        // When this method was called, this means thrown exception was read and won't be available anymore.
        $exception = $this->exception;
        // Reset the exception.
        $this->exception = null;

        return $exception;
    }

    /**
     * @param string $wsdl
     */
    protected function setWSDL($wsdl)
    {
        // Allow "null" and valid URLs.
        $isWsdlValid = null === $wsdl || filter_var($wsdl, FILTER_VALIDATE_URL);
        // Set the URL if it is validated.
        $this->wsdl = $isWsdlValid ? $wsdl : null;

        // Throw deferred exception when WSDL was reset due to it invalidation.
        if (!$isWsdlValid) {
            throw new \InvalidArgumentException(sprintf('You must pass a correct WSDL or null to %s.', __METHOD__));
        }
    }

    /**
     * @param array $options
     */
    protected function setOptions(array $options = null)
    {
        if (null === $options) {
            $this->options = [];
        } else {
            foreach ($options as $option => $value) {
                $this->setOption($option, $value);
            }
        }
    }

    /**
     * @param string $option
     * @param mixed $value
     */
    protected function setOption($option, $value)
    {
        $this->options[$option] = is_string($value) && defined($value) ? constant($value) : $value;
    }

    /**
     * @param array $namespaces
     */
    protected function setNamespaces(array $namespaces = null)
    {
        if (null === $namespaces) {
            $this->namespaces = [];
        } else {
            foreach ($namespaces as $prefix => $uri) {
                $this->setNamespace($prefix, $uri);
            }
        }
    }

    /**
     * @param string $prefix
     * @param string $uri
     */
    protected function setNamespace($prefix, $uri)
    {
        $this->namespaces[$prefix] = $uri;
    }
}
