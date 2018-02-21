<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleIndexer\Indexer\SourceItem;

/**
 * Source Item indexer
 * Check bundle children, if one of them in_stock - make bundle in_stock
 *
 * @api
 */
class SourceItemIndexer
{
    /**
     * @var IndexDataProvider
     */
    private $indexDataProvider;

    /**
     * @var ChildrenSourceItemsIdsProvider
     */
    private $childrenSourceItemsIdsProvider;

    /**
     * @var BundleBySkuAndChildrenSourceItemsIdsIndexer
     */
    private $bundleBySkuAndChildrenSourceItemsIdsIndexer;

    /**
     * @param IndexDataProvider $indexDataProvider
     * @param ChildrenSourceItemsIdsProvider $childrenSourceItemsIdsProvider
     * @param BundleBySkuAndChildrenSourceItemsIdsIndexer $bundleBySkuAndChildrenSourceItemsIdsIndexer
     */
    public function __construct(
        IndexDataProvider $indexDataProvider,
        ChildrenSourceItemsIdsProvider $childrenSourceItemsIdsProvider,
        BundleBySkuAndChildrenSourceItemsIdsIndexer $bundleBySkuAndChildrenSourceItemsIdsIndexer
    ) {
        $this->indexDataProvider = $indexDataProvider;
        $this->childrenSourceItemsIdsProvider = $childrenSourceItemsIdsProvider;
        $this->bundleBySkuAndChildrenSourceItemsIdsIndexer = $bundleBySkuAndChildrenSourceItemsIdsIndexer;
    }

    /**
     * @return void
     */
    public function executeFull()
    {
        $bundleChildrenSourceItemsIdsWithSku = $this->childrenSourceItemsIdsProvider->execute();

        $this->bundleBySkuAndChildrenSourceItemsIdsIndexer->execute($bundleChildrenSourceItemsIdsWithSku);
    }

    /**
     * @param int $sourceItemId
     * @return void
     */
    public function executeRow(int $sourceItemId)
    {
        $this->executeList([$sourceItemId]);
    }

    /**
     * @param array $sourceItemIds
     * @return void
     */
    public function executeList(array $sourceItemIds)
    {
        $bundleChildrenSourceItemsIdsWithSku = $this->childrenSourceItemsIdsProvider->execute($sourceItemIds);

        $this->bundleBySkuAndChildrenSourceItemsIdsIndexer->execute($bundleChildrenSourceItemsIdsWithSku);
    }
}
