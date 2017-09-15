<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\Stock\Command;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Indexer\Alias;
use Magento\Inventory\Indexer\IndexNameBuilder;
use Magento\Inventory\Indexer\IndexNameResolverInterface;
use Magento\Inventory\Indexer\StockItem\IndexStructure as StockItemIndex;
use Magento\Inventory\Indexer\StockItemIndexerInterface;
use Magento\Inventory\Setup\Operation\CreateReservationTable;
use Magento\InventoryApi\Api\Data\ReservationInterface;
use Magento\InventoryApi\Api\GetProductQuantityInStockInterface;

/**
 * Return Quantity of products available to be sold by Product SKU and Stock Id
 *
 * @see \Magento\InventoryApi\Api\GetProductQuantityInStockInterface
 * @api
 */
class GetProductQuantityInStock implements GetProductQuantityInStockInterface
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var IndexNameBuilder
     */
    private $indexNameBuilder;

    /**
     * @var IndexNameResolverInterface
     */
    private $indexNameResolver;

    /**
     * GetProductQuantityInStock constructor.
     *
     * @param ResourceConnection $resource
     * @param IndexNameBuilder $indexNameBuilder
     * @param IndexNameResolverInterface $indexNameResolver
     */
    public function __construct(
        ResourceConnection $resource,
        IndexNameBuilder $indexNameBuilder,
        IndexNameResolverInterface $indexNameResolver
    ) {
        $this->resource = $resource;
        $this->indexNameBuilder = $indexNameBuilder;
        $this->indexNameResolver = $indexNameResolver;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sku, int $stockId): float
    {
        $productQtyInStock = $this->getStockItemQty($sku, $stockId) - $this->getReservationQty($sku, $stockId);
        return (float) $productQtyInStock;
    }

    /**
     * Return product quantity in stock.
     *
     * @param string $sku
     * @param int $stockId
     * @return float
     */
    private function getStockItemQty(string $sku, int $stockId): float
    {
        $indexName = $this->indexNameBuilder
            ->setIndexId(StockItemIndexerInterface::INDEXER_ID)
            ->addDimension('stock_', $stockId)
            ->setAlias(Alias::ALIAS_MAIN)
            ->create();

        $stockItemTableName = $this->indexNameResolver->resolveName($indexName);

        $connection = $this->resource->getConnection();

        $select = $connection->select()
            ->from($stockItemTableName, [StockItemIndex::QUANTITY])
            ->where(StockItemIndex::SKU . '=?', $sku)
            ->limit(1);

        $stockItemQty = $connection->fetchOne($select);
        if (false === $stockItemQty) {
            $stockItemQty = 0;
        }

        return (float) $stockItemQty;
    }

    /**
     * Return the sum of all product reservations in stock.
     *
     * @param string $sku
     * @param int $stockId
     * @return float
     */
    private function getReservationQty(string $sku, int $stockId): float
    {
        $connection = $this->resource->getConnection();

        $reservationTableName = $connection->getTableName(CreateReservationTable::TABLE_NAME_RESERVATION);

        $select = $connection->select()
            ->from($reservationTableName, [ReservationInterface::QUANTITY => 'sum(' . ReservationInterface::QUANTITY . ')'])
            ->where(ReservationInterface::SKU . '=?', $sku)
            ->where(ReservationInterface::STOCK_ID . '=?', $stockId)
            ->limit(1);

        $reservationQty = $connection->fetchOne($select);
        if (false === $reservationQty) {
            $reservationQty = 0;
        }

        return (float) $reservationQty;
    }
}
