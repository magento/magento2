<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System config email field backend model
 */
namespace Magento\Config\Model\Config\Backend\Locale;

use Magento\Framework\Exception\LocalizedException;

/**
 * @api
 * @since 100.0.2
 */
class Timezone extends \Magento\Framework\App\Config\Value
{
    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        if (!in_array($this->getValue(), \DateTimeZone::listIdentifiers(\DateTimeZone::ALL))) {
            throw new LocalizedException(__('The time zone is incorrect. Verify the time zone and try again.'));
        }
        return $this;
    }
}
