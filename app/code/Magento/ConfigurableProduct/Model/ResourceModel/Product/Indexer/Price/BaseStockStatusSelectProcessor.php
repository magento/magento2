<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price;

use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;

/**
 * A Select object processor.
 *
 * Adds stock status limitations to a given Select object.
 */
class BaseStockStatusSelectProcessor implements BaseSelectProcessorInterface
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfig;

    /**
     * @param ResourceConnection $resource
     * @param StockConfigurationInterface $stockConfig
     */
    public function __construct(
        ResourceConnection $resource,
        StockConfigurationInterface $stockConfig
    ) {
        $this->resource = $resource;
        $this->stockConfig = $stockConfig;
    }

    /**
     * @inheritdoc
     */
    public function process(Select $select)
    {
        // Does not make sense to extend query if out of stock products won't appear in tables for indexing
        if ($this->stockConfig->isShowOutOfStock()) {
            $stockIndexTableName = $this->resource->getTableName('cataloginventory_stock_status');
            $select->joinInner(
                ['child_stock_default' => $stockIndexTableName],
                'child_stock_default.product_id = l.product_id',
                []
            )->joinInner(
                ['parent_stock_default' => $stockIndexTableName],
                'parent_stock_default.product_id = le.entity_id',
                []
            )->where(
                'child_stock_default.stock_status = 1 OR parent_stock_default.stock_status = 0'
            );
        }

        return $select;
    }
}
