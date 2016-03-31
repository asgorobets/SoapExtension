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
    private $messages = [];
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

        if (!empty($this->messages)) {
            throw new \RuntimeException(implode("\n", $this->messages));
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
                $this->messages[] = sprintf('Exit code "%s" does not match with expected.', $value);
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
                $this->messages[] = sprintf('Exception message "%s" does not contain expected value.', $value);
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
            $exceptions = count($this->messages);

            // - At least one message needed to be able to choose one from and meet "or" condition.
            // - Two messages are needed to be able to meet "and" condition.
            if (('or' === $this->condition && $exceptions < 2) || ('and' === $this->condition && $exceptions < 1)) {
                $this->messages = [];
            }
        }
    }
}
