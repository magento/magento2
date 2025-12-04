<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

/**
 * Catalog Product List Sort Order
 *
 */
namespace Magento\Catalog\Model\Config\Source;

use Magento\Catalog\Model\ResourceModel\Product\Collection;

class SortOrder implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Retrieve option values array
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => Collection::SORT_ORDER_ASC, 'label' => __('Ascending')],
            ['value' => Collection::SORT_ORDER_DESC, 'label' => __('Descending')],
        ];
    }
}
