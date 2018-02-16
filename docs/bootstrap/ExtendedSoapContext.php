<?php

namespace YourCompanyName\CustomFeatureContexts\Bootstrap;

use Behat\SoapExtension\Context\SoapContext;
use PHPUnit_Framework_Assert as Assertions;

/**
 * Class ExtendedSoapContext
 *
 * Don't forget to add it to composer's autoload!
 *
 * @package YourCompanyName\CustomFeatureContexts\Bootstrap
 */
class ExtendedSoapContext extends SoapContext
{
    /**
     * @var string
     */
    private $a;

    /**
     * @var string
     */
    private $b;

    /**
     * SetUp necessary arguments for custom usage.
     *
     * @param array $args
     */
    public function __construct(array $args)
    {
        $this->a = $args['a'];
        $this->b = $args['b'];
    }

    /**
     * @param string $c
     *
     * @Then /^I want to check that "(.*)" equals to A and not B$/
     */
    public function iCheckThatValueEqualsToANotB($c)
    {
        Assertions::assertEquals($this->a, $c);
        Assertions::assertNotEquals($this->b, $c);
    }
}
