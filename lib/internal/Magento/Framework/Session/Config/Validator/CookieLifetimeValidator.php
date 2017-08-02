<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Session\Config\Validator;

/**
 * Class \Magento\Framework\Session\Config\Validator\CookieLifetimeValidator
 *
 * @since 2.0.0
 */
class CookieLifetimeValidator extends \Magento\Framework\Validator\AbstractValidator
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
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
