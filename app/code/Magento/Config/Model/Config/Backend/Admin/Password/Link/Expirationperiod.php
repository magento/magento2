<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Admin Reset Password Link Expiration period backend model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Config\Model\Config\Backend\Admin\Password\Link;

/**
 * @api
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
        $expirationPeriod = (int)$this->getValue();

        if ($expirationPeriod < 1) {
            $expirationPeriod = (int)$this->getOldValue();
        }
        $this->setValue((string)$expirationPeriod);
        return $this;
    }
}
