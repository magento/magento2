<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
    private const AUTODETECT = 0;

    /**
     * @const int
     */
    private const MANUAL = 1;

    /**
     * Options int
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return  [
            ['value' => self::AUTODETECT, 'label' => __('Auto-Detection (default)')],
            ['value' => self::MANUAL, 'label' => __('Manual Choose')],
        ];
    }
}
