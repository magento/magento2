<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Exception;

abstract class AbstractAggregateException extends LocalizedException
{
    /**
     * The array of errors that have been added via the addError() method.
     *
     * @var ErrorMessage[]
     */
    protected $errors = [];

    /**
     * The original message after being processed by the parent constructor
     *
     * @var string
     */
    protected $originalMessage;

    /**
     * The original raw message passed in via the constructor
     *
     * @var string
     */
    protected $originalRawMessage;

    /**
     * The original params passed in via the constructor
     *
     * @var array
     */
    protected $originalParams = [];

    /**
     * An internal variable indicating how many time addError has been called
     *
     * @var int
     */
    private $addErrorCalls = 0;

    /**
     * Initialize the exception.
     *
     * @param string     $message
     * @param array      $params
     * @param \Exception $cause
     */
    public function __construct($message, array $params = [], \Exception $cause = null)
    {
        $this->originalRawMessage = $message;
        $this->originalParams = $params;
        parent::__construct($message, $params, $cause);
        $this->originalMessage = $this->message;
    }

    /**
     * Create a new error raw message object for the message and its substitution parameters.
     *
     * @param string $rawMessage Exception message
     * @param array  $params  Substitution parameters and extra error debug information
     *
     * @return $this
     */
    public function addError($rawMessage, array $params = [])
    {
        $this->addErrorCalls++;
        if (empty($this->errors)) {
            if (1 === $this->addErrorCalls) {
                // First call: simply overwrite the message and params
                $this->rawMessage = $rawMessage;
                $this->params = $params;
                $this->message = __($rawMessage, $params);
            } elseif (2 === $this->addErrorCalls) {
                // Second call: store the error from the first call and the second call in the array
                // restore the message and params to their original value
                $this->errors[] = new ErrorMessage($this->rawMessage, $this->params);
                $this->errors[] = new ErrorMessage($rawMessage, $params);
                $this->rawMessage = $this->originalRawMessage;
                $this->params = $this->originalParams;
                $this->message = $this->originalMessage;
            }
        } else {
            // All subsequent calls after the second should reach here
            $this->errors[] = new ErrorMessage($rawMessage, $params);
        }
        return $this;
    }

    /**
     * Should return true if someone has added different errors to this exception after construction
     *
     * @return bool
     */
    public function wasErrorAdded()
    {
        return (0 < $this->addErrorCalls);
    }

    /**
     * Return the array of ErrorMessage objects. Return an empty array if no errors were added.
     *
     * @return ErrorMessage[]
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
