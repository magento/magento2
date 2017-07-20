<?php
/**
 * Email address validator
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Validator;

class EmailMinimal implements \Zend_Validate_Interface
{
    const INVALID = 'emailAddressInvalid';

    /**
     * Validation failure message template definitions
     *
     * @var string[]
     */
    private $messageTemplates = [
        self::INVALID => '"%1" is not a valid email address.'
    ];

    /**
     * Validation error messages
     *
     * @var string[] Validation error messages
     */
    private $messages = [];

    /**
     * Validate email address contains '@' sign
     *
     * @param string $value
     *
     * @return bool
     */
    public function isValid($value)
    {
        $this->messages = [];
        if (!is_string($value) || strrpos($value, '@') === false) {
            $translatedMessage = __($this->messageTemplates[self::INVALID], $value);
            $this->messages[] = $translatedMessage;
        }

        return empty($this->_messages);
    }

    /**
     * Return error messages (if any) after the last validation
     *
     * @return string[]
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Sets the validation failure message template for a particular key
     *
     * @param string $messageString
     * @param null   $messageKey OPTIONAL
     *
     * @return $this
     * @throws \InvalidArgumentException If no message template exists for key
     */
    public function setMessage($messageString, $messageKey = null)
    {
        if ($messageKey === null) {
            $keys = array_keys($this->messageTemplates);
            foreach ($keys as $key) {
                $this->setMessage($messageString, $key);
            }

            return $this;
        }

        if (!isset($this->messageTemplates[$messageKey])) {
            throw new \InvalidArgumentException(
                sprintf('No message template exists for key %s', $messageKey)
            );
        }

        $this->messageTemplates[$messageKey] = $messageString;

        return $this;
    }
}
