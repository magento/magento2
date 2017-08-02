<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Source\Web;

/**
 * @api
 * @since 2.0.0
 */
class Redirect implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('No')],
            ['value' => 1, 'label' => __('Yes (302 Found)')],
            ['value' => 301, 'label' => __('Yes (301 Moved Permanently)')]
        ];
    }
}
