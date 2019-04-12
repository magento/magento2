<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStock\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;

/**
 * Class GetStockIndexDump provides sku and qty of products dumping them from stock index table
 */
class GetStockIndexDump
{
    /**
     * @var StockIndexTableNameResolverInterface
     */
    private $stockIndexTableNameResolver;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * GetStockIndexDump constructor
     *
     * @param StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        ResourceConnection $resourceConnection

    ) {
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Provides sku and qty of products dumping them from stock index table
     *
     * @param array $products
     * @param int $stockId
     * @return array
     */
    public function execute(array $products, int $stockId): array
    {
        $stockIndexTableName = $this->stockIndexTableNameResolver->execute($stockId);
        $tableName = $this->resourceConnection->getTableName($stockIndexTableName);
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($tableName)
            ->columns(
                [
                    'sku' => 'sku',
                    'qty' => 'quantity',
                ]
            )->where(
                'sku IN (?)',
                $this->getProductSkus($products)
            );

        return $connection->fetchAll($select);
    }

    /**
     * Provides list of product skus by product array
     *
     * @param array $products
     * @return string[]
     */
    private function getProductSkus(array $products): array
    {
        $skus = [];
        foreach ($products as $product) {
            $skus[] = $product->getSku();
        }

        return $skus;
    }
}
