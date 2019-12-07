<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Config\Source;

class ListMode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'grid', 'label' => __('Grid Only')],
            ['value' => 'list', 'label' => __('List Only')],
            ['value' => 'grid-list', 'label' => __('Grid (default) / List')],
            ['value' => 'list-grid', 'label' => __('List (default) / Grid')]
        ];
    }
}
