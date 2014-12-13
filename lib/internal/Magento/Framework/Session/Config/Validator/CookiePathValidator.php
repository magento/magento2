<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
