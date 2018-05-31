<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Model\Indexer;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Item;
use Magento\CatalogInventory\Model\Stock;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\PriceModifierInterface;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructure;

/**
 * Class for filter product price index.
 */
class ProductPriceIndexFilter implements PriceModifierInterface
{
    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var Item
     */
    private $stockItem;

    /**
     * @param StockConfigurationInterface $stockConfiguration
     * @param Item $stockItem
     */
    public function __construct(StockConfigurationInterface $stockConfiguration, Item $stockItem)
    {
        $this->stockConfiguration = $stockConfiguration;
        $this->stockItem = $stockItem;
    }

    /**
     * Remove out of stock products data from price index.
     *
     * @param IndexTableStructure $priceTable
     * @param array $entityIds
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function modifyPrice(IndexTableStructure $priceTable, array $entityIds = [])
    {
        if ($this->stockConfiguration->isShowOutOfStock()) {
            return;
        }

        $connection = $this->stockItem->getConnection();
        $select = $connection->select();
        $select->from(
            ['price_index' => $priceTable->getTableName()],
            []
        );
        $select->joinInner(
            ['stock_item' => $this->stockItem->getMainTable()],
            'stock_item.product_id = price_index.' . $priceTable->getEntityField()
            . ' AND stock_item.stock_id = ' . Stock::DEFAULT_STOCK_ID,
            []
        );
        if ($this->stockConfiguration->getManageStock()) {
            $stockStatus = $connection->getCheckSql(
                'use_config_manage_stock = 0 AND manage_stock = 0',
                Stock::STOCK_IN_STOCK,
                'is_in_stock'
            );
        } else {
            $stockStatus = $connection->getCheckSql(
                'use_config_manage_stock = 0 AND manage_stock = 1',
                'is_in_stock',
                Stock::STOCK_IN_STOCK
            );
        }
        $select->where($stockStatus . ' = ?', Stock::STOCK_OUT_OF_STOCK);

        $query = $select->deleteFromSelect('price_index');
        $connection->query($query);
    }
}
