<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Session\Config\Validator;

/**
 * Session cookie domain validator
 */
class CookieDomainValidator extends \Magento\Framework\Validator\AbstractValidator
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

        $validator = new \Laminas\Validator\Hostname(\Laminas\Validator\Hostname::ALLOW_ALL);

        if (!empty($value) && !$validator->isValid($value)) {
            $this->_addMessages($validator->getMessages());
            return false;
        }
        return true;
    }
}
