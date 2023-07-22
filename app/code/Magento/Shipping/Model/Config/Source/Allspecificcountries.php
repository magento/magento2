<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Allspecificcountries implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('All Allowed Countries')],
            ['value' => 1, 'label' => __('Specific Countries')]
        ];
    }
}
