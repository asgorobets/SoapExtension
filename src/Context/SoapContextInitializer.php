<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */
namespace Behat\SoapExtension\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;

/**
 * Class SoapContextInitializer.
 *
 * @package Behat\SoapExtension\Context
 */
class SoapContextInitializer implements ContextInitializer
{
    /**
     * Parameters of SoapExtension.
     *
     * @var array
     */
    private $parameters = [];

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function initializeContext(Context $context)
    {
        if ($context instanceof SoapContextInterface) {
            $context->setSoapParameters($this->parameters);
        }
    }
}
