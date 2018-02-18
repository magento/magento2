<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\ResourceModel\Stock;

use Magento\Inventory\Model\GetStockItemDataInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;

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
    public function execute(string $sku, int $stockId)
    {
        $stockItemTableName = $this->stockIndexTableNameResolver->execute($stockId);

        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($stockItemTableName, [IndexStructure::QUANTITY, IndexStructure::IS_SALABLE])
            ->where(IndexStructure::SKU . ' = ?', $sku);

        return $connection->fetchRow($select) ?: null;
    }
}
