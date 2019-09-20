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
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ObjectManager;

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
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var string
     */
    private $connectionName;

    /**
     * @param StockConfigurationInterface $stockConfiguration
     * @param Item $stockItem
     * @param ResourceConnection $resourceConnection
     * @param string $connectionName
     */
    public function __construct(
        StockConfigurationInterface $stockConfiguration,
        Item $stockItem,
        ResourceConnection $resourceConnection = null,
        $connectionName = 'indexer'
    ) {
        $this->stockConfiguration = $stockConfiguration;
        $this->stockItem = $stockItem;
        $this->resourceConnection = $resourceConnection ?: ObjectManager::getInstance()->get(ResourceConnection::class);
        $this->connectionName = $connectionName;
    }

    /**
     * Remove out of stock products data from price index.
     *
     * @param IndexTableStructure $priceTable
     * @param array $entityIds
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function modifyPrice(IndexTableStructure $priceTable, array $entityIds = []) : void
    {
        if ($this->stockConfiguration->isShowOutOfStock()) {
            return;
        }

        $connection = $this->resourceConnection->getConnection($this->connectionName);
        $stockSelect = $connection->select();
        $stockSelect->from(
            $this->stockItem->getMainTable(),
            [
                'product_id',
            ]
        );
        if (!empty($entityIds)) {
            $stockSelect->where('product_id IN (?)', $entityIds);
        }
        $stockSelect->group('product_id');
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
        $stockStatus = new \Zend_Db_Expr('MAX(' . $stockStatus . ')');
        $stockSelect->having($stockStatus . ' = ' . Stock::STOCK_OUT_OF_STOCK);
        $productIds = $connection->fetchCol($stockSelect);

        if (!empty($productIds)) {
            $where = [$priceTable->getEntityField() .' IN (?)' => $productIds];
            $connection->delete($priceTable->getTableName(), $where);
        }
    }
}
