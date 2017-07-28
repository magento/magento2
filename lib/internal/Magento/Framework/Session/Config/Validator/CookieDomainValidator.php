<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Session\Config\Validator;

/**
 * Class \Magento\Framework\Session\Config\Validator\CookieDomainValidator
 *
 * @since 2.0.0
 */
class CookieDomainValidator extends \Magento\Framework\Validator\AbstractValidator
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function isValid($value)
    {
        $this->_clearMessages();
        if (!is_string($value)) {
            $this->_addMessages(['must be a string']);
            return false;
        }

        $validator = new \Zend\Validator\Hostname(\Zend\Validator\Hostname::ALLOW_ALL);

        if (!empty($value) && !$validator->isValid($value)) {
            $this->_addMessages($validator->getMessages());
            return false;
        }
        return true;
    }
}
