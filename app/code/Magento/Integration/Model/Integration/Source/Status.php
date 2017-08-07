<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model\Integration\Source;

/**
 * Integration status options.
 */
class Status implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Retrieve status options array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => \Magento\Integration\Model\Integration::STATUS_INACTIVE, 'label' => __('Inactive')],
            ['value' => \Magento\Integration\Model\Integration::STATUS_ACTIVE, 'label' => __('Active')],
            ['value' => \Magento\Integration\Model\Integration::STATUS_RECREATED, 'label' => __('Reset')]
        ];
    }
}
