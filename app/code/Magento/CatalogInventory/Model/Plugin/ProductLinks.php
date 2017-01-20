<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @var Configuration
     */
    private $configuration;

    /**
     * @var Stock
     */
    private $stockHelper;

    /**
     * ProductLinks constructor.
     *
     * @param Configuration $configuration
     * @param Stock $stockHelper
     */
    public function __construct(Configuration $configuration, Stock $stockHelper)
    {
        $this->configuration = $configuration;
        $this->stockHelper = $stockHelper;
    }

    /**
     * @param Link $subject
     * @param Collection $collection
     * @return Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetProductCollection(Link $subject, Collection $collection)
    {
        if ($this->configuration->isShowOutOfStock() != 1) {
            $this->stockHelper->addInStockFilterToCollection($collection);
        }
        return $collection;
    }
}
