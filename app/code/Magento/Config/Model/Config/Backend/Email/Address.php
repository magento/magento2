<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System config email field backend model
 */
namespace Magento\Config\Model\Config\Backend\Email;

use Magento\Framework\Exception\LocalizedException;

/**
 * @api
 * @since 100.0.2
 */
class Address extends \Magento\Framework\App\Config\Value
{
    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if (!\Zend_Validate::is($value, \Magento\Framework\Validator\EmailAddress::class)) {
            throw new LocalizedException(__('Please correct the email address: "%1".', $value));
        }
        return $this;
    }
}
