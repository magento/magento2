<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System config email field backend model
 */
namespace Magento\Config\Model\Config\Backend\Email;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validator\EmailAddress;
use Magento\Framework\Validator\ValidateException;
use Magento\Framework\Validator\ValidatorChain;

/**
 * @api
 * @since 100.0.2
 */
class Address extends Value
{
    /**
     * Processing object before save data
     *
     * @return $this
     * @throws LocalizedException|ValidateException
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if (!ValidatorChain::is($value, EmailAddress::class)) {
            throw new LocalizedException(
                __('The "%1" email address is incorrect. Verify the email address and try again.', $value)
            );
        }
        return $this;
    }
}
