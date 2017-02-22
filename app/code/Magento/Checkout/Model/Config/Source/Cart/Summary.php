<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model\Config\Source\Cart;

class Summary implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Display number of items in cart')],
            ['value' => 1, 'label' => __('Display item quantities')]
        ];
    }
}
