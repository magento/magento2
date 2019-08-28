<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryGroupedProductIndexer\Indexer\SourceItem;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\GroupedProduct\Model\ResourceModel\Product\Link;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;
use Magento\InventoryIndexer\Indexer\SourceItem\SkuListInStock;
use Magento\InventoryIndexer\Indexer\SourceItem\SkuListInStockFactory;

/**
 * Returns relations between stock and sku list
 */
class SiblingSkuListInStockProvider
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var SkuListInStockFactory
     */
    private $skuListInStockFactory;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var string
     */
    private $tableNameSourceItem;

    /**
     * @var string
     */
    private $tableNameStockSourceLink;

    /**
     * @param ResourceConnection $resourceConnection
     * @param SkuListInStockFactory $skuListInStockFactory
     * @param MetadataPool $metadataPool
     * @param string $tableNameSourceItem
     * @param string $tableNameStockSourceLink
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        SkuListInStockFactory $skuListInStockFactory,
        MetadataPool $metadataPool,
        $tableNameSourceItem,
        $tableNameStockSourceLink
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->skuListInStockFactory = $skuListInStockFactory;
        $this->metadataPool = $metadataPool;
        $this->tableNameSourceItem = $tableNameSourceItem;
        $this->tableNameStockSourceLink = $tableNameStockSourceLink;
    }

    /**
     * Returns all assigned Stock ids by given Source Item ids
     *
     * @param int[] $sourceItemIds
     * @return SkuListInStock[] List of stock id to sku1,sku2 assignment
     * @throws Exception
     */
    public function execute(array $sourceItemIds): array
    {
        $connection = $this->resourceConnection->getConnection();
        $sourceStockLinkTable = $this->resourceConnection->getTableName($this->tableNameStockSourceLink);
        $sourceItemTable = $this->resourceConnection->getTableName($this->tableNameSourceItem);

        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $metadata->getIdentifierField();
        $items = [];

        $select = $connection
            ->select()
            ->from(
                ['source_item' => $sourceItemTable],
                [SourceItemInterface::SKU => 'sibling_product_entity.' . SourceItemInterface::SKU]
            )->joinInner(
                ['stock_source_link' => $sourceStockLinkTable],
                sprintf(
                    'source_item.%s = stock_source_link.%s',
                    SourceItemInterface::SOURCE_CODE,
                    StockSourceLinkInterface::SOURCE_CODE
                ),
                [StockSourceLinkInterface::STOCK_ID]
            )->joinInner(
                ['child_product_entity' => $this->resourceConnection->getTableName('catalog_product_entity')],
                'child_product_entity.sku = source_item.sku',
                []
            )->joinInner(
                ['parent_link' => $this->resourceConnection->getTableName('catalog_product_link')],
                'parent_link.linked_product_id = child_product_entity.' . $linkField
                    . ' AND parent_link.link_type_id = ' . Link::LINK_TYPE_GROUPED,
                []
            )->joinInner(
                ['sibling_link' => $this->resourceConnection->getTableName('catalog_product_link')],
                'sibling_link.product_id = parent_link.product_id 
                    AND parent_link.link_type_id = ' . Link::LINK_TYPE_GROUPED,
                []
            )->joinInner(
                ['sibling_product_entity' => $this->resourceConnection->getTableName('catalog_product_entity')],
                'sibling_product_entity.' . $linkField . ' = sibling_link.linked_product_id',
                []
            )->where(
                'source_item.source_item_id IN (?)',
                $sourceItemIds
            );

        $dbStatement = $connection->query($select);
        while ($item = $dbStatement->fetch()) {
            $items[$item[StockSourceLinkInterface::STOCK_ID]][$item[SourceItemInterface::SKU]] =
                $item[SourceItemInterface::SKU];
        }

        return $this->getStockIdToSkuList($items);
    }

    /**
     * Return the assigned stock id to sku list
     *
     * @param array $items
     * @return SkuListInStock[]
     */
    private function getStockIdToSkuList(array $items): array
    {
        $skuListInStockList = [];
        foreach ($items as $stockId => $skuList) {
            /** @var SkuListInStock $skuListInStock */
            $skuListInStock = $this->skuListInStockFactory->create();
            $skuListInStock->setStockId((int)$stockId);
            $skuListInStock->setSkuList($skuList);
            $skuListInStockList[] = $skuListInStock;
        }
        return $skuListInStockList;
    }
}
