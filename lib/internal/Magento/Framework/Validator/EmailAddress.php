<?php
/**
 * Email address validator
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator;

class EmailAddress extends \Zend_Validate_EmailAddress implements \Magento\Framework\Validator\ValidatorInterface
{
    public function isValid($value)
    {
        if (false === \strpos($value, '@')) {
            $this->_error(self::INVALID);
            return false;
        }
        
        return true;
    }
}
