<?php
/**
 * Copyright © 2015-17 Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\LoginAsCustomer\Model\Config\Source;

/**
 * Class StoreViewLogin
 * @package Magefan\LoginAsCustomer\Model\Config\Source
 */
class StoreViewLogin implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @const int
     */
    const AUTODETECT = 0;

    /**
     * @const int
     */
    const MANUAL = 1;
    
    /**
     * Options int
     *
     * @return array
     */
    public function toOptionArray()
    {
        return  [
            ['value' => self::AUTODETECT, 'label' => __('Auto-Detection (default)')],
            ['value' => self::MANUAL, 'label' => __('Manual Choose')],
        ];
    }
}
