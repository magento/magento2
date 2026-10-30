<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Config\Model\Config\Source\Web;

/**
 * @api
 * @since 100.0.2
 */
class Redirect implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
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
