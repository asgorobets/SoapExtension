<?php
/**
 * @author Alexei Gorobet, <asgorobets@gmail.com>
 */
namespace Behat\SoapExtension\Context;

use Symfony\Component\Yaml\Yaml;
use PHPUnit_Framework_Assert as Assertions;
// Argument processors.
use Behat\Gherkin\Node\TableNode;
use Behat\Gherkin\Node\PyStringNode;
// Utils.
use Behat\SoapExtension\Utils\SoapFaultProcessor;
// Scopes.
use Behat\Behat\Hook\Scope\BeforeStepScope;

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
    private $fault;

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
     * Send SOAP request with function arguments array as YAML.
     *
     * @Given I call SOAP function :function with arguments array as YAML:
     */
    public function iSendRequestYAML($function, PyStringNode $arguments)
    {
        $arguments = Yaml::parse($arguments->getRaw());
        $this->sendRequest($function, $arguments);
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
     * @Then /^(?:|I )expect SOAP exception(?:| with code "(\d+)")(?:|( and| or)? with message "([^"]+?)")$/
     */
    public function expectException($code = null, $condition = null, $message = null)
    {
        // Exit with an error because we're expected an exception and got nothing.
        if (null === $this->fault) {
            throw new \RuntimeException('Expected \SoapFault exception was not thrown!');
        }

        new SoapFaultProcessor($this->fault, $code, $message, $condition);

        // If processor didn't throw an exception, then we shouldn't too.
        $this->fault = null;
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

    /**
     * @BeforeStep
     */
    public function beforeStepCheckForException(BeforeStepScope $scope)
    {
        // Check for SOAP exception from previously executed step.
        $this->fault = $this->getException();

        // @todo Is it really a better way to do this?
        if (null !== $this->fault && strpos($scope->getStep()->getText(), 'SOAP exception') === false) {
            throw $this->fault;
        }
    }
}
