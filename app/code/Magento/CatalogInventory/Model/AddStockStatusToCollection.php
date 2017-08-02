<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model;

use Magento\Catalog\Model\ResourceModel\Product\Collection;

/**
 * Catalog inventory module plugin
 * @since 2.1.0
 */
class AddStockStatusToCollection
{
    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     * @since 2.1.0
     */
    protected $stockHelper;
    
    /**
     * @param \Magento\CatalogInventory\Model\Configuration $configuration
     * @param \Magento\CatalogInventory\Helper\Stock $stockHelper
     * @since 2.1.0
     */
    public function __construct(
        \Magento\CatalogInventory\Helper\Stock $stockHelper
    ) {
        $this->stockHelper = $stockHelper;
    }

    /**
     * @param Collection $productCollection
     * @param bool $printQuery
     * @param bool $logQuery
     * @return array
     * @since 2.1.0
     */
    public function beforeLoad(Collection $productCollection, $printQuery = false, $logQuery = false)
    {
        $this->stockHelper->addIsInStockFilterToCollection($productCollection);
        return [$printQuery, $logQuery];
    }
}
