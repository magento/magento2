<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Session\Config\Validator;

/**
 * Class \Magento\Framework\Session\Config\Validator\CookiePathValidator
 *
 */
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
