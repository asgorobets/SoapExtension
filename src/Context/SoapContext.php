<?php
/**
 * @author Alexei Gorobet, <asgorobets@gmail.com>
 */
namespace Behat\SoapExtension\Context;

use Behat\Behat\Hook\Scope\AfterStepScope;
use Symfony\Component\Yaml\Yaml;
use Behat\Gherkin\Node\TableNode;
use Behat\Gherkin\Node\PyStringNode;
use PHPUnit_Framework_Assert as Assertions;

/**
 * Class SoapContext.
 *
 * @package Behat\SoapExtension\Context
 *
 * @todo Rename methods.
 * @todo Document methods.
 * @todo Make steps more flexible with regex.
 */
class SoapContext extends RawSoapContext
{
    /**
     * @var mixed
     */
    private $value;

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
        $this->setWSDL($wsdl);
    }

    /**
     * Sets the WSDL for the next SOAP request to NULL.
     *
     * @Given I am working with SOAP service in non-WSDL mode
     */
    public function iAmWorkingWithSoapServiceNoWSDL()
    {
        $this->setWSDL(null);
    }

    /**
     * @Given I am working with SOAP service with options list:
     */
    public function iAmWorkingWithSoapServiceWithOptions(TableNode $options)
    {
        foreach ($options->getRowsHash() as $option => $value) {
            $this->setOption($option, $value);
        }
    }

    /**
     * @Given I am working with SOAP service with options as YAML:
     */
    public function iAmWorkingWithSoapServiceWithOptionsYaml(PyStringNode $options)
    {
        foreach (Yaml::parse($options->getRaw()) as $option => $value) {
            $this->setOption($option, $value);
        }
    }

    /**
     * Send SOAP request with params list.
     *
     * @Given I call SOAP function :function with params list:
     */
    public function iSendRequestWithParams($function, TableNode $params)
    {
        $this->sendRequest($function, [$params->getRowsHash()]);
    }

    /**
     * Send SOAP request with raw body.
     *
     * @Given I call SOAP with raw body:
     */
    public function iSendRequestBody(PyStringNode $body)
    {
        // Tell SOAP we want to send the body as XML, if not otherwise specified.
        $this->setOption('use', SOAP_LITERAL);
        $this->setOption('style', SOAP_DOCUMENT);
        $this->sendRequest('MethodNameIsIgnored', [new \SoapVar($body->getRaw(), XSD_ANYXML)]);
    }

    /**
     * Throw exceptions in case there were any we didn't expect and SoapContext
     * has an exception saved.
     *
     * @AfterStep */
    public function afterStepThrowExceptions(AfterStepScope $scope)
    {
        if (!preg_match('/^I call SOAP/', $scope->getStep()->getText())
          && $e = $this->getException()) {
            throw $e;
        }
    }

    /**
     * @Given I register the following XPATH namespaces:
     */
    public function iRegisterXpathNamespaces(TableNode $namespaces)
    {
        foreach ($namespaces->getRowsHash() as $prefix => $uri) {
            $this->setNamespace($prefix, $uri);
        }
    }

    /**
     * @Then /^I should get SOAP error matching "(.*)"$/
     */
    public function iShouldGetSoapErrorMatching($error_pattern)
    {
        $error = '';
        if ($exception = $this->getException()) {
            $error = $exception->getMessage();
        }
        Assertions::assertRegExp($error_pattern, $error);
        $this->setException(null);
    }

    /**
     * @Given I should see SOAP response property :property equals to :text
     */
    public function iShouldSeeSoapResponsePropertyEquals($text, $property)
    {
        Assertions::assertEquals($text, $this->extractResponseProperty($property));
    }

    /**
     * @Given I should see SOAP response property :property is not :text
     */
    public function iShouldSeeSoapResponsePropertyNotEquals($text, $property)
    {
        Assertions::assertNotEquals($text, $this->extractResponseProperty($property));
    }

    /**
     * @Given I should see SOAP response property :property contains :text
     */
    public function iShouldSeeSoapResponsePropertyContains($text, $property)
    {
        Assertions::assertContains($text, $this->extractResponseProperty($property));
    }

    /**
     * @Given I should see SOAP response property :property doesn't contain :text
     */
    public function iShouldSeeSoapResponsePropertyNotContains($text, $property)
    {
        Assertions::assertNotContains($text, $this->extractResponseProperty($property));
    }

    /**
     * @Then I should see SOAP response property :property matching pattern :pattern
     */
    public function iShouldSeeSoapResponsePropertyMatches($pattern, $property)
    {
        Assertions::assertRegExp($pattern, $this->extractResponseProperty($property));
    }

    /**
     * @Then I should see that SOAP Response matches XPATH :xpath
     */
    public function iShouldSeeThatSOAPResponseMatchesXpath($xpath)
    {
        Assertions::assertTrue(
            $this->extractResponseValueMatchingXPATH($xpath) !== false,
            "Couldn't find node matching provided XPATH: "
        );
    }

    /**
     * @Given I am working with SOAP response property :property
     */
    public function iWorkWithResponseProperty($property)
    {
        $this->value = $this->extractResponseProperty($property);
    }

    /**
     * @Given I am working with SOAP element matching XPATH :xpath
     */
    public function iWorkWithElementTextMatchingXPATH($xpath)
    {
        $this->value = $this->extractResponseValueMatchingXPATH($xpath);
    }

    /**
     * @Then saved SOAP value equals to :text
     */
    public function savedValueEquals($text)
    {
        Assertions::assertEquals($text, $this->value);
    }

    /**
     * @Then saved SOAP value is not equal to :text
     */
    public function savedValueNotEquals($text)
    {
        Assertions::assertNotEquals($text, $this->value);
    }

    /**
     * @Then saved SOAP value contains :text
     */
    public function savedValueContains($text)
    {
        Assertions::assertContains($text, $this->value);
    }

    /**
     * @Then saved SOAP value doesn't contain :text
     */
    public function savedValueNotContains($text)
    {
        Assertions::assertNotContains($text, $this->value);
    }

    /**
     * @Then saved SOAP value matches :pattern
     */
    public function savedValueMatchesRegExp($pattern)
    {
        Assertions::assertRegExp($pattern, $this->value);
    }

    /**
     * @Then saved SOAP value doesn't match :pattern
     */
    public function savedValueNotMatchesRegExp($pattern)
    {
        Assertions::assertNotRegExp($pattern, $this->value);
    }
}
