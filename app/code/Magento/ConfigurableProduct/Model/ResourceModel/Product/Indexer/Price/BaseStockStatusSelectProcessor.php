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
            $select->join(
                ['si' => $this->resource->getTableName('cataloginventory_stock_item')],
                'si.product_id = l.product_id',
                []
            );
            $select->join(
                ['si_parent' => $this->resource->getTableName('cataloginventory_stock_item')],
                'si_parent.product_id = l.parent_id',
                []
            );
            $select->where('si.is_in_stock = ?', Stock::STOCK_IN_STOCK);
            $select->orWhere('si_parent.is_in_stock = ?', Stock::STOCK_OUT_OF_STOCK);
        }

        return $select;
    }
}
