<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Config\Backend\Password\Link;

/**
 * Customer Reset Password Link Expiration period backend model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Expirationperiod extends \Magento\Framework\App\Config\Value
{
    /**
     * Validate expiration period value before saving
     *
     * @return $this
     * @since 2.0.0
     */
    public function beforeSave()
    {
        parent::beforeSave();
        $resetPasswordLinkExpirationPeriod = (int)$this->getValue();

        if ($resetPasswordLinkExpirationPeriod < 1) {
            $resetPasswordLinkExpirationPeriod = (int)$this->getOldValue();
        }
        $this->setValue((string)$resetPasswordLinkExpirationPeriod);
        return $this;
    }
}
