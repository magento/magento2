<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Config\Source;

/**
 * Options provider for weight units list
 *
 * @api
 * @since 2.0.0
 */
class WeightUnit implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return [['value' => 'lbs', 'label' => __('lbs')], ['value' => 'kgs', 'label' => __('kgs')]];
    }
}
