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

/**
 * Class \Magento\CatalogInventory\Model\Plugin\ProductLinks
 *
 * @since 2.1.0
 */
class ProductLinks
{
    /**
     * @var Configuration
     * @since 2.1.0
     */
    private $configuration;

    /**
     * @var Stock
     * @since 2.1.0
     */
    private $stockHelper;

    /**
     * ProductLinks constructor.
     *
     * @param Configuration $configuration
     * @param Stock $stockHelper
     * @since 2.1.0
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
     * @since 2.1.0
     */
    public function afterGetProductCollection(Link $subject, Collection $collection)
    {
        if ($this->configuration->isShowOutOfStock() != 1) {
            $this->stockHelper->addInStockFilterToCollection($collection);
        }
        return $collection;
    }
}
