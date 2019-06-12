<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Model\Indexer;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Item;
<<<<<<< HEAD
use Magento\CatalogInventory\Model\Stock;
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\PriceModifierInterface;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructure;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ObjectManager;
<<<<<<< HEAD
=======
use Magento\Framework\DB\Query\Generator;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

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
<<<<<<< HEAD
=======
     * @var Generator
     */
    private $batchQueryGenerator;

    /**
     * @var int
     */
    private $batchSize;

    /**
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @param StockConfigurationInterface $stockConfiguration
     * @param Item $stockItem
     * @param ResourceConnection $resourceConnection
     * @param string $connectionName
<<<<<<< HEAD
=======
     * @param Generator $batchQueryGenerator
     * @param int $batchSize
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    public function __construct(
        StockConfigurationInterface $stockConfiguration,
        Item $stockItem,
        ResourceConnection $resourceConnection = null,
<<<<<<< HEAD
        $connectionName = 'indexer'
=======
        $connectionName = 'indexer',
        Generator $batchQueryGenerator = null,
        $batchSize = 100
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    ) {
        $this->stockConfiguration = $stockConfiguration;
        $this->stockItem = $stockItem;
        $this->resourceConnection = $resourceConnection ?: ObjectManager::getInstance()->get(ResourceConnection::class);
        $this->connectionName = $connectionName;
<<<<<<< HEAD
=======
        $this->batchQueryGenerator = $batchQueryGenerator ?: ObjectManager::getInstance()->get(Generator::class);
        $this->batchSize = $batchSize;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
    public function modifyPrice(IndexTableStructure $priceTable, array $entityIds = [])
=======
    public function modifyPrice(IndexTableStructure $priceTable, array $entityIds = []) : void
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        if ($this->stockConfiguration->isShowOutOfStock()) {
            return;
        }

        $connection = $this->resourceConnection->getConnection($this->connectionName);
        $select = $connection->select();
<<<<<<< HEAD
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
=======

        $select->from(
            ['stock_item' => $this->stockItem->getMainTable()],
            ['stock_item.product_id', 'MAX(stock_item.is_in_stock) as max_is_in_stock']
        );

        if ($this->stockConfiguration->getManageStock()) {
            $select->where('stock_item.use_config_manage_stock = 1 OR stock_item.manage_stock = 1');
        } else {
            $select->where('stock_item.use_config_manage_stock = 0 AND stock_item.manage_stock = 1');
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
                $productIds[] = $row['product_id'];
            }
            if ($productIds !== null) {
                $where = [$priceTable->getEntityField() .' IN (?)' => $productIds];
                $connection->delete($priceTable->getTableName(), $where);
            }
        }
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    }
}
