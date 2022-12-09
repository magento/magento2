<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Model\Indexer;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Item;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\PriceModifierInterface;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructure;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Query\Generator;

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
     * @var Generator
     */
    private $batchQueryGenerator;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @param StockConfigurationInterface $stockConfiguration
     * @param Item $stockItem
     * @param ResourceConnection $resourceConnection
     * @param string $connectionName
     * @param Generator $batchQueryGenerator
     * @param int $batchSize
     */
    public function __construct(
        StockConfigurationInterface $stockConfiguration,
        Item $stockItem,
        ResourceConnection $resourceConnection = null,
        $connectionName = 'indexer',
        Generator $batchQueryGenerator = null,
        $batchSize = 100
    ) {
        $this->stockConfiguration = $stockConfiguration;
        $this->stockItem = $stockItem;
        $this->resourceConnection = $resourceConnection ?: ObjectManager::getInstance()->get(ResourceConnection::class);
        $this->connectionName = $connectionName;
        $this->batchQueryGenerator = $batchQueryGenerator ?: ObjectManager::getInstance()->get(Generator::class);
        $this->batchSize = $batchSize;
    }

    /**
     * Remove out of stock products data from price index.
     *
     * @param IndexTableStructure $priceTable
     * @param array $entityIds
     * @return void
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function modifyPrice(IndexTableStructure $priceTable, array $entityIds = []) : void
    {
        if ($this->stockConfiguration->isShowOutOfStock()) {
            return;
        }

        $connection = $this->resourceConnection->getConnection($this->connectionName);
        $select = $connection->select();

        $select->from(
            ['stock_item' => $this->stockItem->getMainTable()],
            ['stock_item.product_id', 'MAX(stock_item.is_in_stock) as max_is_in_stock']
        );

        if ($this->stockConfiguration->getManageStock()) {
            $select->where('stock_item.use_config_manage_stock = 1 OR stock_item.manage_stock = 1');
        } else {
            $select->where('stock_item.use_config_manage_stock = 0 AND stock_item.manage_stock = 1');
        }

        if (!empty($entityIds)) {
            $select->where('stock_item.product_id IN (?)', $entityIds, \Zend_Db::INT_TYPE);
        }

        $select->group('stock_item.product_id');
        $select->having('max_is_in_stock = 0');

        $batchSelectIterator = $this->batchQueryGenerator->generate(
            'product_id',
            $select,
            $this->batchSize,
            \Magento\Framework\DB\Query\BatchIteratorInterface::UNIQUE_FIELD_ITERATOR
        );

        foreach ($batchSelectIterator as $select) {
            $productIds = null;
            foreach ($connection->query($select)->fetchAll() as $row) {
                $productIds[] = (int) $row['product_id'];
            }
            if ($productIds !== null) {
                $where = [$priceTable->getEntityField() .' IN (?)' => $productIds];
                $connection->delete($priceTable->getTableName(), $where);
            }
        }
    }
}
