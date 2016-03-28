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
class RawSoapContext implements SoapContextInterface
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
     *
     * @see SoapManager::setOptions()
     * @see SoapManager::setNamespaces()
     */
    protected function setWSDL($wsdl)
    {
        // Initialize SOAP manager with predefined values from configuration.
        foreach (['options', 'namespaces'] as $param) {
            $method = 'set' . ucfirst($param);

            // Unset all options and namespaces and initialize them from config.
            foreach ([null, $this->getSoapParameter($param)] as $value) {
                call_user_func([$this, $method], $value);
            }
        }

        $this->soapWSDL($wsdl);
    }
}
