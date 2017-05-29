<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Exception;

use Magento\Framework\Phrase;

/**
 * @api
 */
abstract class AbstractAggregateException extends LocalizedException
{
    /**
     * The array of errors that have been added via the addError() method
     *
     * @var \Magento\Framework\Exception\LocalizedException[]
     */
    protected $errors = [];

    /**
     * The original phrase
     *
     * @var \Magento\Framework\Phrase
     */
    protected $originalPhrase;

    /**
     * An internal variable indicating how many time addError has been called
     *
     * @var int
     */
    private $addErrorCalls = 0;

    /**
     * Initialize the exception
     *
     * @param \Magento\Framework\Phrase $phrase
     * @param \Exception $cause
     */
    public function __construct(Phrase $phrase, \Exception $cause = null)
    {
        $this->originalPhrase = $phrase;
        parent::__construct($phrase, $cause);
    }

    /**
     * Add new error into the list of exceptions
     *
     * @param \Magento\Framework\Phrase $phrase
     * @return $this
     */
    public function addError(Phrase $phrase)
    {
        $this->addErrorCalls++;
        if (empty($this->errors)) {
            if (1 === $this->addErrorCalls) {
                // First call: simply overwrite the phrase and message
                $this->phrase = $phrase;
                $this->message = $phrase->render();
                $this->logMessage = null;
            } elseif (2 === $this->addErrorCalls) {
                // Second call: store the error from the first call and the second call in the array
                // restore the phrase to its original value
                $this->errors[] = new LocalizedException($this->phrase);
                $this->errors[] = new LocalizedException($phrase);
                $this->phrase = $this->originalPhrase;
                $this->message = $this->originalPhrase->render();
                $this->logMessage = null;
            }
        } else {
            // All subsequent calls after the second should reach here
            $this->errors[] = new LocalizedException($phrase);
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
     * Get the array of LocalizedException objects. Get an empty array if no errors were added.
     *
     * @return \Magento\Framework\Exception\LocalizedException[]
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
