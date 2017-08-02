<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model\Rule\Action;

/**
 * Class \Magento\CatalogRule\Model\Rule\Action\SimpleActionOptionsProvider
 *
 * @since 2.1.0
 */
class SimpleActionOptionsProvider implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @return array
     * @since 2.1.0
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('Apply as percentage of original'),
                'value' => 'by_percent'
            ],
            [
                'label' => __('Apply as fixed amount'),
                'value' => 'by_fixed'
            ],
            [
                'label' => __('Adjust final price to this percentage'),
                'value' => 'to_percent'
            ],
            [
                'label' => __('Adjust final price to discount value'),
                'value' => 'to_fixed'
            ]
        ];
    }
}
