<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Session\Config\Validator;

class CookiePathValidator extends \Magento\Framework\Validator\AbstractValidator
{
    /**
     * {@inheritdoc}
     */
    public function isValid($value)
    {
        $this->_clearMessages();
        $test = parse_url($value, PHP_URL_PATH);
        if ($test != $value || '/' != $test[0]) {
            return false;
        }
        return true;
    }
}
