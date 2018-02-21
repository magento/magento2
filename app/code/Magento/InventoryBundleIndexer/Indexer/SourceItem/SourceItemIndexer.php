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
     * @var BundlesIndexDataProvider
     */
    private $bundlesIndexDataProvider;

    /**
     * @var BundleChildrenSourceItemsIdsProvider
     */
    private $bundleChildrenSourceItemsIdsProvider;

    /**
     * @var BundleBySkuAndChildrenSourceItemsIdsIndexer
     */
    private $bundleBySkuAndChildrenSourceItemsIdsIndexer;

    /**
     * @param BundlesIndexDataProvider $bundlesIndexDataProvider
     * @param BundleChildrenSourceItemsIdsProvider $bundleChildrenSourceItemsIdsProvider
     * @param BundleBySkuAndChildrenSourceItemsIdsIndexer $bundleBySkuAndChildrenSourceItemsIdsIndexer
     */
    public function __construct(
        BundlesIndexDataProvider $bundlesIndexDataProvider,
        BundleChildrenSourceItemsIdsProvider $bundleChildrenSourceItemsIdsProvider,
        BundleBySkuAndChildrenSourceItemsIdsIndexer $bundleBySkuAndChildrenSourceItemsIdsIndexer
    ) {
        $this->bundlesIndexDataProvider = $bundlesIndexDataProvider;
        $this->bundleChildrenSourceItemsIdsProvider = $bundleChildrenSourceItemsIdsProvider;
        $this->bundleBySkuAndChildrenSourceItemsIdsIndexer = $bundleBySkuAndChildrenSourceItemsIdsIndexer;
    }

    /**
     * @return void
     */
    public function executeFull()
    {
        $bundleChildrenSourceItemsIdsWithSku = $this->bundleChildrenSourceItemsIdsProvider->execute();

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
        $bundleChildrenSourceItemsIdsWithSku = $this->bundleChildrenSourceItemsIdsProvider->execute($sourceItemIds);

        $this->bundleBySkuAndChildrenSourceItemsIdsIndexer->execute($bundleChildrenSourceItemsIdsWithSku);
    }
}
