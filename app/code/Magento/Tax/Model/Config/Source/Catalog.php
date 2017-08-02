<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Config\Source;

/**
 * Class \Magento\Tax\Model\Config\Source\Catalog
 *
 * @since 2.0.0
 */
class Catalog implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('No (price without tax)')],
            ['value' => 1, 'label' => __('Yes (only price with tax)')],
            ['value' => 2, 'label' => __("Both (without and with tax)")]
        ];
    }
}
