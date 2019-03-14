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
     * @param ResourceConnection $resource
     * @param StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     */
    public function __construct(
        ResourceConnection $resource,
        StockIndexTableNameResolverInterface $stockIndexTableNameResolver
    ) {
        $this->resource = $resource;
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sku, int $stockId): ?array
    {
        $stockItemTableName = $this->stockIndexTableNameResolver->execute($stockId);

        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from(
                $stockItemTableName,
                [
                    GetStockItemDataInterface::QUANTITY => IndexStructure::QUANTITY,
                    GetStockItemDataInterface::IS_SALABLE => IndexStructure::IS_SALABLE,
                ]
            )
            ->where(IndexStructure::SKU . ' = ?', $sku);

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
