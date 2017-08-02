<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model\Source;

/**
 * Class \Magento\Shipping\Model\Source\HandlingAction
 *
 * @since 2.0.0
 */
class HandlingAction implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => \Magento\Shipping\Model\Carrier\AbstractCarrier::HANDLING_ACTION_PERORDER,
                'label' => __('Per Order'),
            ],
            [
                'value' => \Magento\Shipping\Model\Carrier\AbstractCarrier::HANDLING_ACTION_PERPACKAGE,
                'label' => __('Per Package')
            ]
        ];
    }
}
