<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Session\Config\Validator;

class CookieLifetimeValidator extends \Magento\Framework\Validator\AbstractValidator
{
    /**
     * {@inheritdoc}
     */
    public function isValid($value)
    {
        $this->_clearMessages();
        if (!is_numeric($value)) {
            $this->_addMessages(['must be numeric']);
            return false;
        }
        if ($value < 0) {
            $this->_addMessages(['must be a positive integer or zero']);
            return false;
        }
        return true;
    }
}
