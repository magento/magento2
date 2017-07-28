<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model\Config\Source\Cart;

/**
 * Class \Magento\Checkout\Model\Config\Source\Cart\Summary
 *
 * @since 2.0.0
 */
class Summary implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Display number of items in cart')],
            ['value' => 1, 'label' => __('Display item quantities')]
        ];
    }
}
