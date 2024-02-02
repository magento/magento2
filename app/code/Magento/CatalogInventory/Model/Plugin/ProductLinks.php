<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\Plugin;

use Magento\Catalog\Model\Product\Link;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection;
use Magento\CatalogInventory\Helper\Stock;
use Magento\CatalogInventory\Model\Configuration;

class ProductLinks
{

    /**
     * ProductLinks constructor.
     *
     * @param Configuration $configuration
     * @param Stock $stockHelper
     */
    public function __construct(
        private readonly Configuration $configuration,
        private readonly Stock $stockHelper
    ) {
    }

    /**
     * Fixes simple products are shown as associated in grouped when set out of stock
     *
     * @param Link $subject
     * @param Collection $collection
     * @return Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetProductCollection(Link $subject, Collection $collection)
    {
        if ($this->configuration->isShowOutOfStock() != 1) {
            $this->stockHelper->addIsInStockFilterToCollection($collection);
        }
        return $collection;
    }
}
