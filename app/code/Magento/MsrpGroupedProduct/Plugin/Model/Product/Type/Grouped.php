<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MsrpGroupedProduct\Plugin\Model\Product\Type;

use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection;

/**
 * Minimum advertised price plugin for grouped product
 */
class Grouped
{
    /**
     * Add minimum advertised price to the attribute selection for associated products
     *
     * @param \Magento\GroupedProduct\Model\Product\Type\Grouped $subject
     * @param Collection $collection
     * @return Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAssociatedProductCollection(
        \Magento\GroupedProduct\Model\Product\Type\Grouped $subject,
        Collection $collection
    ): Collection {
        $collection->addAttributeToSelect(['msrp']);
        return $collection;
    }
}
