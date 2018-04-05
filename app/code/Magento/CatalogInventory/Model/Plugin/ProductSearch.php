<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogInventory\Model\Plugin;

use Magento\CatalogInventory\Helper\Stock;
use Magento\CatalogInventory\Model\Configuration;

class ProductSearch
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
