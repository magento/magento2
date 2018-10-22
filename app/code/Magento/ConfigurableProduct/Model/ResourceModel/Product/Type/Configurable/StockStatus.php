<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;

use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\Data\StockStatusInterface as CatalogInventoryStockStatusInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

class StockStatus implements StockStatusInterface
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * StockStatus constructor.
     * @param ResourceConnection $resource
     * @param MetadataPool $metadataPool
     */
    public function __construct(ResourceConnection $resource, MetadataPool $metadataPool)
    {
        $this->resource = $resource;
        $this->metadataPool = $metadataPool;
    }

    /**
     * @var array
     */
    private $allChildOutOfStockInfo = [];

    /**
     * @inheritdoc
     */
    public function isAllChildOutOfStock(int $productId): bool
    {
        if (isset($this->allChildOutOfStockInfo[$productId])) {
            return $this->allChildOutOfStockInfo[$productId];
        }

        $statuses = $this->getAllChildStockInfo($productId);
        $isAllChildOutOfStock = true;
        foreach ($statuses as $status) {
            if ($status === CatalogInventoryStockStatusInterface::STATUS_IN_STOCK) {
                $isAllChildOutOfStock = false;
                break;
            }
        }

        $this->allChildOutOfStockInfo[$productId] = $isAllChildOutOfStock;
        return $this->allChildOutOfStockInfo[$productId];
    }

    /**
     * @return AdapterInterface
     */
    protected function getConnection(): AdapterInterface
    {
        return $this->resource->getConnection();
    }

    /**
     * @param int $productId
     * @return array
     * @throws \Exception
     */
    private function getAllChildStockInfo(int $productId): array
    {
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $productTable = $this->resource->getTableName('catalog_product_entity');
        $productRelationTable = $this->resource->getTableName('catalog_product_relation');

        $select = $this->getConnection()->select()
            ->from(['parent' => $productTable], '', [])
            ->joinInner(
                ['link' => $productRelationTable],
                "link.parent_id = parent.$linkField",
                ['id' => 'child_id']
            )->joinInner(
                ['stock' => $this->resource->getTableName('cataloginventory_stock_status')],
                'stock.product_id = link.child_id',
                ['stock_status']
            )->where(sprintf('parent.%s = ?', $linkField), $productId);

        return $this->getConnection()->fetchPairs($select);
    }
}
