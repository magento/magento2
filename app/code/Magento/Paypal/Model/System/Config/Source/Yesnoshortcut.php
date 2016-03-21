<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\System\Config\Source;

/**
 * Used in creating options for Yes|No config value selection
 */
class Yesnoshortcut implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('Yes (PayPal recommends this option)')],
            ['value' => 0, 'label' => __('No')]
        ];
    }
}
