<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Shipping\Model\Carrier\AbstractCarrier;

class HandlingAction implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => AbstractCarrier::HANDLING_ACTION_PERORDER,
                'label' => __('Per Order'),
            ],
            [
                'value' => AbstractCarrier::HANDLING_ACTION_PERPACKAGE,
                'label' => __('Per Package')
            ]
        ];
    }
}
