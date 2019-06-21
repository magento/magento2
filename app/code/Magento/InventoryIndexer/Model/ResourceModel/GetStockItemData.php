<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;
use Magento\InventorySalesApi\Model\GetStockItemDataInterface;
use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventoryCatalogApi\Model\GetProductIdsBySkusInterface;

/**
 * @inheritdoc
 */
class GetStockItemData implements GetStockItemDataInterface
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var StockIndexTableNameResolverInterface
     */
    private $stockIndexTableNameResolver;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @var GetProductIdsBySkusInterface
     */
    private $getProductIdsBySkus;

    /**
     * @param ResourceConnection $resource
     * @param StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     */
    public function __construct(
        ResourceConnection $resource,
        StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        DefaultStockProviderInterface $defaultStockProvider,
        GetProductIdsBySkusInterface $getProductIdsBySkus
    ) {
        $this->resource = $resource;
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->defaultStockProvider = $defaultStockProvider;
        $this->getProductIdsBySkus = $getProductIdsBySkus;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sku, int $stockId): ?array
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select();

        if ($this->defaultStockProvider->getId() === $stockId) {
            $productId = current($this->getProductIdsBySkus->execute([$sku]));
            $stockItemTableName = $this->resource->getTableName('cataloginventory_stock_status');
            $select->from(
                $stockItemTableName,
                [
                    GetStockItemDataInterface::QUANTITY => 'qty',
                    GetStockItemDataInterface::IS_SALABLE => 'stock_status',
                ]
            )->where('product_id' . ' = ?', $productId);
        } else {
            $stockItemTableName = $this->stockIndexTableNameResolver->execute($stockId);
            $select->from(
                $stockItemTableName,
                [
                    GetStockItemDataInterface::QUANTITY => IndexStructure::QUANTITY,
                    GetStockItemDataInterface::IS_SALABLE => IndexStructure::IS_SALABLE,
                ]
            )->where(IndexStructure::SKU . ' = ?', $sku);
        }

        try {
            if ($connection->isTableExists($stockItemTableName)) {
                return $connection->fetchRow($select) ?: null;
            }

            return null;
        } catch (\Exception $e) {
            throw new LocalizedException(__(
                'Could not receive Stock Item data'
            ), $e);
        }
    }
}
