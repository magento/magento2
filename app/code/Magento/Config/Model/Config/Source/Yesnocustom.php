<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Yes|No|Specified config value selection
 *
 */
namespace Magento\Config\Model\Config\Source;

/**
 * @api
 * @since 2.0.0
 */
class Yesnocustom implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     * @since 2.0.0
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
