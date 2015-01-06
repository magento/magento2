<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Shipping\Model\Source;

class HandlingType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
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
