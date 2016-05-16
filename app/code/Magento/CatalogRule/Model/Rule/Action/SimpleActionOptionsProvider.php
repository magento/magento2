<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model\Rule\Action;

class SimpleActionOptionsProvider implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @return array
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
