<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Shipping\Model\Carrier\AbstractCarrier;

class HandlingType implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => AbstractCarrier::HANDLING_TYPE_FIXED,
                'label' => __('Fixed'),
            ],
            [
                'value' => AbstractCarrier::HANDLING_TYPE_PERCENT,
                'label' => __('Percent')
            ]
        ];
    }
}
