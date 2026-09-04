<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Config\Model\Config\Source\Dev;

/**
 * @api
 * @since 100.0.2
 */
class Dbautoup implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => \Magento\Framework\App\ResourceConnection::AUTO_UPDATE_ALWAYS,
                'label' => __('Always (during development)')
            ],
            [
                'value' => \Magento\Framework\App\ResourceConnection::AUTO_UPDATE_ONCE,
                'label' => __('Only Once (version upgrade)')
            ],
            [
                'value' => \Magento\Framework\App\ResourceConnection::AUTO_UPDATE_NEVER,
                'label' => __('Never (production)')
            ]
        ];
    }
}
