<?php
/**
 * \Exception class for validator
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Phrase;
use Magento\Framework\Message\AbstractMessage;
use Magento\Framework\Message\Error;

/**
 * Exception to be thrown when data validation fails
 *
 * @api
 */
class Exception extends InputException
{
    /**
     * @var array
     */
    protected $messages = [];

    /**
     * Constructor
     *
     * @param \Magento\Framework\Phrase $phrase
     * @param \Exception $cause
     * @param array $messages Validation error messages
     * @param int $code
     */
    public function __construct(
        Phrase $phrase = null,
        \Exception $cause = null,
        array $messages = [],
        $code = 0
    ) {
        if (!empty($messages)) {
            $message = '';
            foreach ($messages as $propertyMessages) {
                foreach ($propertyMessages as $propertyMessage) {
                    if ($message) {
                        $message .= PHP_EOL;
                    }
                    $message .= $propertyMessage;
                    $this->addMessage(new Error($propertyMessage));
                }
            }
            $phrase = new Phrase($message);
        }
        parent::__construct($phrase, $cause, $code);
    }

    /**
     * Setter for message
     *
     * @param \Magento\Framework\Message\AbstractMessage $message
     * @return $this
     */
    public function addMessage(AbstractMessage $message)
    {
        if (!isset($this->messages[$message->getType()])) {
            $this->messages[$message->getType()] = [];
        }
        $this->messages[$message->getType()][] = $message;
        return $this;
    }

    /**
     * Getter for messages by type or all
     *
     * @param string $type
     * @return array
     */
    public function getMessages($type = '')
    {
        if ('' == $type) {
            $allMessages = [];
            foreach ($this->messages as $messages) {
                $allMessages = array_merge($allMessages, $messages);
            }
            return $allMessages;
        }
        return isset($this->messages[$type]) ? $this->messages[$type] : [];
    }
}
