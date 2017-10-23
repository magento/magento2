<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Source\Web;

/**
 * @api
 * @since 100.0.2
 */
class Protocol implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => ''],
            ['value' => 'http', 'label' => __('HTTP (unsecure)')],
            ['value' => 'https', 'label' => __('HTTPS (SSL)')]
        ];
    }
}
