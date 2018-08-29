<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Model\Config\SessionLifetime;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\LocalizedException;

/**
 * Backend model for the admin/security/session_lifetime configuration field. Validates session lifetime.
 * @api
 * @since 100.1.0
 */
class BackendModel extends Value
{
    /** Maximum dmin session lifetime; 1 year*/
    const MAX_LIFETIME = 31536000;

    /** Minimum admin session lifetime */
    const MIN_LIFETIME = 60;

    /**
     * @since 100.1.0
     */
    public function beforeSave()
    {
        $value = (int)$this->getValue();
        if ($value > self::MAX_LIFETIME) {
            throw new LocalizedException(
                __(
                    'The Admin session lifetime is invalid. '
                    . 'Set the lifetime to 31536000 seconds (one year) or shorter and try again.'
                )
            );
        } elseif ($value < self::MIN_LIFETIME) {
            throw new LocalizedException(
                __('The Admin session lifetime is invalid. Set the lifetime to 60 seconds or longer and try again.')
            );
        }
        return parent::beforeSave();
    }
}
