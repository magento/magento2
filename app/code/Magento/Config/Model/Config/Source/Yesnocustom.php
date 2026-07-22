<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/**
 * Used in creating options for Yes|No|Specified config value selection
 *
 */
namespace Magento\Config\Model\Config\Source;

/**
 * @api
 * @since 100.0.2
 */
class Yesnocustom implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('Yes')],
            ['value' => 0, 'label' => __('No')],
            ['value' => 2, 'label' => __('Specified')]
        ];
    }
}
