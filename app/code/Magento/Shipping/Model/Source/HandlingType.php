<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model\Source;

/**
 * Class \Magento\Shipping\Model\Source\HandlingType
 *
 * @since 2.0.0
 */
class HandlingType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => \Magento\Shipping\Model\Carrier\AbstractCarrier::HANDLING_TYPE_FIXED,
                'label' => __('Fixed'),
            ],
            [
                'value' => \Magento\Shipping\Model\Carrier\AbstractCarrier::HANDLING_TYPE_PERCENT,
                'label' => __('Percent')
            ]
        ];
    }
}
