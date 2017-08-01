<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\LayeredNavigation\Model\Attribute\Source;

/**
 * @api
 * @since 2.0.0
 */
class FilterableOptions implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
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
