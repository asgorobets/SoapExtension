<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */
namespace Behat\SoapExtension\Context;

use Behat\Behat\Context\Context;

/**
 * Interface SoapContextInterface.
 *
 * @package Behat\SoapExtension\Context
 */
interface SoapContextInterface extends Context
{
    /**
     * Set parameters from behat.yml.
     *
     * @param array $parameters
     *   An array of parameters from configuration file.
     */
    public function setSoapParameters(array $parameters);
}
