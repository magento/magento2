<?php
/**
 * Email address validator
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator;

class Email extends AbstractValidator implements \Magento\Framework\Validator\ValidatorInterface
{
    const INVALID            = 'emailAddressInvalid';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array();

    /**
     * Validate email address contains '@' sign
     *
     * @param mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        $isValid = true;
        if (!is_string($value) || strrpos($value, '@') === false) {
            $isValid = false;
            $this->_error(self::INVALID, $value);
        }

        return $isValid;
    }

    /**
     * Sets the validation failure message template for a particular key
     *
     * @param  string $messageString
     * @param  string $messageKey     OPTIONAL
     * @return AbstractValidator Provides a fluent interface
     * @throws Exception
     */
    public function setMessage($messageString, $messageKey = null)
    {
        if ($messageKey === null) {
            $keys = array_keys($this->_messageTemplates);
            foreach($keys as $key) {
                $this->setMessage($messageString, $key);
            }
            return $this;
        }

        if (!isset($this->_messageTemplates[$messageKey])) {
            $exceptionPhrase = new \Magento\Framework\Phrase("No message template exists for key '$messageKey'");
            throw new Exception($exceptionPhrase);
        }

        $this->_messageTemplates[$messageKey] = $messageString;
        return $this;
    }

    /**
     * @param  string $messageKey
     * @param  string $value      OPTIONAL
     * @return void
     */
    protected function _error($messageKey, $value = null)
    {
        if ($messageKey === null) {
            $keys = array_keys($this->_messageTemplates);
            $messageKey = current($keys);
        }

        $this->_messages[$messageKey] = $this->_createMessage($messageKey, $value);
    }

    /**
     * Constructs and returns a validation failure message with the given message key and value.
     *
     * Returns null if and only if $messageKey does not correspond to an existing template.
     *
     * If a translator is available and a translation exists for $messageKey,
     * the translation will be used.
     *
     * @param  string $messageKey
     * @param  string $value
     * @return string
     */
    protected function _createMessage($messageKey, $value)
    {
        if(!isset($this->_messageTemplates[$messageKey])){
            return null;
        }

        return $this->_messageTemplates[$messageKey];
    }
}
