<?php
/**
 * @author Alexei Gorobet, <asgorobets@gmail.com>
 */
namespace Behat\SoapExtension\Context;

use Behat\SoapExtension\Utils\SoapManager;

/**
 * Class RawSoapContext.
 *
 * @package Behat\SoapExtension\Context
 */
class RawSoapContext extends \PHPUnit_Framework_Assert implements SoapContextInterface
{
    use SoapManager {
        setWSDL as soapWSDL;
    }

    /**
     * Parameters of SoapExtension.
     *
     * @var array
     */
    private $parameters = [];

    /**
     * {@inheritdoc}
     */
    public function setSoapParameters(array $parameters)
    {
        if (empty($this->parameters)) {
            $this->parameters = $parameters;
        }
    }

    /**
     * @param string $name
     *   The name of parameter from behat.yml.
     *
     * @return mixed
     */
    protected function getSoapParameter($name)
    {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : false;
    }

    /**
     * {@inheritdoc}
     */
    protected function setWSDL($wsdl)
    {
        // Initialize SOAP manager with predefined values from configuration.
        foreach (['option', 'namespace'] as $param) {
            $plural = $param . 's';

            // Execute: "unsetOptions" and "unsetNamespaces".
            call_user_func([$this, 'unset' . ucfirst($plural)]);

            foreach ($this->getSoapParameter($plural) as $key => $value) {
                // Execute: "setOption" and "setNamespace".
                call_user_func([$this, 'set' . ucfirst($param)], $key, $value);
            }
        }

        $this->soapWSDL($wsdl);
    }
}
