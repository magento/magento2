<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Search\EngineResolverInterface;
use Magento\Search\Model\EngineResolver;

/**
 * Catalog inventory module plugin
 */
class AddStockStatusToCollection
{
    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    protected $stockHelper;

    /**
     * @var EngineResolverInterface
     */
    private $engineResolver;

    /**
     * @param \Magento\CatalogInventory\Helper\Stock $stockHelper
     * @param EngineResolverInterface $engineResolver
     */
    public function __construct(
        \Magento\CatalogInventory\Helper\Stock $stockHelper,
        EngineResolverInterface $engineResolver
    ) {
        $this->stockHelper = $stockHelper;
        $this->engineResolver = $engineResolver;
    }

    /**
     * Add stock filter to collection.
     *
     * @param Collection $productCollection
     * @param bool $printQuery
     * @param bool $logQuery
     * @return array
     */
    public function beforeLoad(Collection $productCollection, $printQuery = false, $logQuery = false)
    {
        if ($this->engineResolver->getCurrentSearchEngine() === EngineResolver::CATALOG_SEARCH_MYSQL_ENGINE) {
            $this->stockHelper->addIsInStockFilterToCollection($productCollection);
        }
        return [$printQuery, $logQuery];
    }
}
