<?php
/**
 * @author Sergii Bondarenko, <sb@firstvector.org>
 */
namespace Behat\SoapExtension\Utils;

/**
 * Class SoapFaultProcessor.
 *
 * @package Behat\SoapExtension\Utils
 */
class SoapFaultProcessor
{
    /**
     * An exception thrown by \SoapCall.
     *
     * @var \SoapFault
     */
    private $exception;
    /**
     * Messages collected during processing.
     *
     * @var string[]
     */
    private $errors = [];
    /**
     * Expected error code.
     *
     * @var null|string
     */
    private $code;
    /**
     * Expected error message.
     *
     * @var null|string
     */
    private $message;
    /**
     * Condition between comparison of error code and message.
     *
     * @var null|string
     */
    private $condition;

    /**
     * SoapFaultProcessor constructor.
     *
     * @param \SoapFault $exception
     * @param null|string $code
     *   Any numeric value. Type will be casted to "int".
     * @param null|string $message
     *   Expected message. Inaccurate matching will be used.
     * @param null|string $condition
     *   Allowed values: "or", "and".
     */
    public function __construct(\SoapFault $exception, $code = null, $message = null, $condition = null)
    {
        $this->code = $code;
        $this->message = $message;
        $this->condition = empty($condition) ? '' : trim($condition);
        $this->exception = $exception;
    }

    /**
     * Process the data.
     */
    public function __destruct()
    {
        $this->processCode()
          ->processMessage()
          ->processCondition();

        if (!empty($this->errors)) {
            throw new \RuntimeException(implode("\n", $this->errors));
        }
    }

    /**
     * Process expected exit code.
     *
     * @return $this
     */
    private function processCode()
    {
        if (null !== $this->code) {
            $value = $this->exception->getCode();

            if ($value !== (int) $this->code) {
                $this->errors[] = sprintf('Exit code "%s" does not match with expected.', $value);
            }
        }

        return $this;
    }

    /**
     * Process expected error message.
     *
     * @return $this
     */
    private function processMessage()
    {
        if (null !== $this->message) {
            $value = $this->exception->getMessage();

            if (strpos($value, trim($this->message)) === false) {
                $this->errors[] = sprintf('Exception message "%s" does not contain expected value.', $value);
            }
        }

        return $this;
    }

    /**
     * Filter messages depending on condition.
     */
    private function processCondition()
    {
        if (!empty($this->condition)) {
            $errors = count($this->errors);

            // - At least one message needed to be able to choose one from and meet "or" condition.
            // - No failed assertions should be present in order to meet "and" condition.
            if (('or' === $this->condition && $errors < 2) || ('and' === $this->condition && $errors < 1)) {
                $this->errors = [];
            }
        }
    }
}
