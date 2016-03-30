<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */
namespace Behat\SoapExtension\Tests;

use Behat\SoapExtension\Utils\SoapManager;

/**
 * Class SoapManagerTest.
 *
 * @package Behat\SoapExtension\Tests
 */
class SoapManagerTest extends \PHPUnit_Framework_TestCase
{
    use SoapManager;

    /**
     * @test
     */
    public function testSetWSDL()
    {
        $property = 'wsdl';

        // Allow only two types of data: null or valid URL.
        foreach ([null, 'http://correct.link'] as $value) {
            $this->setWSDL($value);
            static::assertAttributeEquals($value, $property, $this);
        }

        // WSDL URL with wildcard protocol is not valid for \SoapClient.
        foreach ([true, false, '', -1.2, 0, 1, '3000', '//correct.link'] as $value) {
            try {
                $this->setWSDL($value);
                // An exception must be thrown when incorrect type passed.
                $this->fail(sprintf('The "%s" property accept a "%s" value but must not!', $property, $value));
            } catch (\InvalidArgumentException $e) {
                // Ensure that incorrect value was not set.
                static::assertAttributeEquals(null, $property, $this);
            }
        }
    }
}
