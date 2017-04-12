<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Config\Source;

class WeightUnit implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [['value' => 'lbs', 'label' => __('lbs')], ['value' => 'kgs', 'label' => __('kgs')]];
    }
}
