<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogInventory\Model\Plugin;

use Magento\CatalogInventory\Helper\Stock;

/** Plugin to add stock filter depends on configuration */
class ProductSearch
{
    /**
     * @var Stock
     */
    private $stockHelper;

    /**
     * @param Stock $stockHelper
     */
    public function __construct(Stock $stockHelper)
    {
        $this->stockHelper = $stockHelper;
    }

    /**
     * Adds stock filter depends on configuration
     *
     * @param \Magento\Catalog\Model\ProductLink\Search $subject
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterPrepareCollection(
        \Magento\Catalog\Model\ProductLink\Search $subject,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
    ): \Magento\Catalog\Model\ResourceModel\Product\Collection {
        $this->stockHelper->addIsInStockFilterToCollection($collection);
        return $collection;
    }
}
