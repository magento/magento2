<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Config\Source;

/**
 * Class \Magento\Tax\Model\Config\Source\Catalog
 *
 */
class Catalog implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
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
