<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Config\SessionLifetime;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\LocalizedException;

/**
 * Backend model for the admin/security/session_lifetime configuration field. Validates session lifetime.
 */
class BackendModel extends Value
{
    /** Maximum dmin session lifetime; 1 year*/
    const MAX_LIFETIME = 31536000;

    /** Minimum admin session lifetime */
    const MIN_LIFETIME = 60;

    public function beforeSave()
    {
        $value = (int) $this->getValue();
        if ($value > self::MAX_LIFETIME) {
            throw new LocalizedException(
                __('Admin session lifetime must be less than or equal to 31536000 seconds (one year)')
            );
        } else if ($value < self::MIN_LIFETIME) {
            throw new LocalizedException(
                __('Admin session lifetime must be greater than or equal to 60 seconds')
            );
        }
        return parent::beforeSave();
    }
}
