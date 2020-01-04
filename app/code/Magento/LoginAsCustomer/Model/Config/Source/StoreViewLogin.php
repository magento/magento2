<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\LoginAsCustomer\Model\Config\Source;

/**
 * Class StoreViewLogin
 * @package Magento\LoginAsCustomer\Model\Config\Source
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
