<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Model\System\Config\Backend;

/**
 * Backend model for processing Public content cache lifetime settings
 *
 * Class Ttl
 */
class Ttl extends \Magento\Framework\App\Config\Value
{
    /**
     * Throw exception if Ttl data is invalid or empty
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if ($value < 0 || !preg_match('/^[0-9]+$/', $value)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Ttl value "%1" is not valid. Please use only numbers equal or greater than zero.', $value)
            );
        }
        return $this;
    }
}
