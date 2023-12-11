<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Session\Config\Validator;

use Laminas\Validator\Hostname;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Session cookie domain validator
 */
class CookieDomainValidator extends AbstractValidator
{
    /**
     * @inheritDoc
     */
    public function isValid($value)
    {
        $this->_clearMessages();

        if (!is_string($value)) {
            $this->_addMessages(['must be a string']);

            return false;
        }

        //Hostname validator allows [;,] and returns the validator as true but,
        //these are unacceptable cookie domain characters hence need explicit validation for the same
        if (preg_match('/[;,]/', $value)) {
            $this->_addMessages(['invalid character in cookie domain']);

            return false;
        }

        $validator = new Hostname(Hostname::ALLOW_ALL);

        if (!empty($value) && !$validator->isValid($value)) {
            $this->_addMessages($validator->getMessages());

            return false;
        }

        return true;
    }
}
