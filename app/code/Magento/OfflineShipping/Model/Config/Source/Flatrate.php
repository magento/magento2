<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflineShipping\Model\Config\Source;

/**
 * @api
 * @since 2.0.0
 */
class Flatrate implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('None')],
            ['value' => 'O', 'label' => __('Per Order')],
            ['value' => 'I', 'label' => __('Per Item')]
        ];
    }
}
