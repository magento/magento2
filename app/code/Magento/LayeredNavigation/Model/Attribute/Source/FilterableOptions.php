<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\LayeredNavigation\Model\Attribute\Source;

class FilterableOptions implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 0,
                'label' => __('No'),
            ],
            [
                'value' => 1,
                'label' => __('Filterable (with results)'),
            ],
            [
                'value' => 2,
                'label' => __('Filterable (no results)'),
            ],
        ];
    }
}
