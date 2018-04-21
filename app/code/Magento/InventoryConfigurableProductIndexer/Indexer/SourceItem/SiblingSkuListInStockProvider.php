<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProductIndexer\Indexer\SourceItem;

use Exception;
use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\SourceItem as SourceItemResourceModel;
use Magento\Inventory\Model\ResourceModel\StockSourceLink as StockSourceLinkResourceModel;
use Magento\Inventory\Model\StockSourceLink;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryIndexer\Indexer\SourceItem\SkuListInStock;
use Magento\InventoryIndexer\Indexer\SourceItem\SkuListInStockFactory;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Api\Data\ProductInterface;

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
     * @var int
     */
    private $groupConcatMaxLen;
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * GetSkuListInStock constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param SkuListInStockFactory $skuListInStockFactory
     * @param MetadataPool $metadataPool
     * @param int $groupConcatMaxLen
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        SkuListInStockFactory $skuListInStockFactory,
        MetadataPool $metadataPool,
        int $groupConcatMaxLen
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->skuListInStockFactory = $skuListInStockFactory;
        $this->groupConcatMaxLen = $groupConcatMaxLen;
        $this->metadataPool = $metadataPool;
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
        $sourceStockLinkTable = $this->resourceConnection->getTableName(
            StockSourceLinkResourceModel::TABLE_NAME_STOCK_SOURCE_LINK
        );
        $sourceItemTable = $this->resourceConnection->getTableName(
            SourceItemResourceModel::TABLE_NAME_SOURCE_ITEM
        );

        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $metadata->getLinkField();

        $select = $connection
            ->select()
            ->from(
                ['source_item' => $sourceItemTable],
                [
                    SourceItemInterface::SKU =>
                        "GROUP_CONCAT(DISTINCT sibling_product_entity." . SourceItemInterface::SKU . " SEPARATOR ',')"
                ]
            )->joinInner(
                ['stock_source_link' => $sourceStockLinkTable],
                sprintf(
                    'source_item.%s = stock_source_link.%s',
                    SourceItemInterface::SOURCE_CODE,
                    StockSourceLink::SOURCE_CODE
                ),
                [StockSourceLink::STOCK_ID]
            )->joinInner(
                ['child_product_entity' => $this->resourceConnection->getTableName('catalog_product_entity')],
                'child_product_entity.sku = source_item.sku',
                []
            )->joinInner(
                ['parent_link' => $this->resourceConnection->getTableName('catalog_product_super_link')],
                'parent_link.product_id = child_product_entity.' . $linkField,
                []
            )->joinInner(
                ['sibling_link' => $this->resourceConnection->getTableName('catalog_product_super_link')],
                'sibling_link.parent_id = parent_link.parent_id',
                []
            )->joinInner(
                ['sibling_product_entity' => $this->resourceConnection->getTableName('catalog_product_entity')],
                'sibling_product_entity.' . $linkField . ' = sibling_link.product_id',
                []
            )->where('source_item.source_item_id IN (?)', $sourceItemIds)
            ->group(['stock_source_link.' . StockSourceLink::STOCK_ID]);

        $connection->query('SET group_concat_max_len = ' . $this->groupConcatMaxLen);
        $items = $connection->fetchAll($select);
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
        foreach ($items as $item) {
            /** @var SkuListInStock $skuListInStock */
            $skuListInStock = $this->skuListInStockFactory->create();
            $skuListInStock->setStockId((int)$item[StockSourceLink::STOCK_ID]);
            $skuListInStock->setSkuList(explode(',', $item[SourceItemInterface::SKU]));
            $skuListInStockList[] = $skuListInStock;
        }
        return $skuListInStockList;
    }
}
