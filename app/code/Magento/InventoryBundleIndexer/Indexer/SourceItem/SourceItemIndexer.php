<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleIndexer\Indexer\SourceItem;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\MultiDimensionalIndexer\Alias;
use Magento\Framework\MultiDimensionalIndexer\IndexHandlerInterface;
use Magento\Framework\MultiDimensionalIndexer\IndexNameBuilder;
use Magento\InventoryIndexer\Indexer\InventoryIndexer;

/**
 * Source Item indexer
 * Check bundle children, if one of them in_stock - make bundle in_stock
 *
 * @api
 */
class SourceItemIndexer
{
    /**
     * @var ChildrenSourceItemsIdsProvider
     */
    private $childrenSourceItemsIdsProvider;

    /**
     * @var ByBundleSkuAndChildrenSourceItemsIdsIndexer
     */
    private $byBundleSkuAndChildrenSourceItemsIdsIndexer;

    /**
     * @param ChildrenSourceItemsIdsProvider $childrenSourceItemsIdsProvider
     * @param ByBundleSkuAndChildrenSourceItemsIdsIndexer $byBundleSkuAndChildrenSourceItemsIdsIndexer
     */
    public function __construct(
        ChildrenSourceItemsIdsProvider $childrenSourceItemsIdsProvider,
        ByBundleSkuAndChildrenSourceItemsIdsIndexer $byBundleSkuAndChildrenSourceItemsIdsIndexer
    ) {
        $this->childrenSourceItemsIdsProvider = $childrenSourceItemsIdsProvider;
        $this->byBundleSkuAndChildrenSourceItemsIdsIndexer = $byBundleSkuAndChildrenSourceItemsIdsIndexer;
    }

    /**
     * @return void
     */
    public function executeFull()
    {
        $bundleChildrenSourceItemsIdsWithSku = $this->childrenSourceItemsIdsProvider->execute();

        if (count($bundleChildrenSourceItemsIdsWithSku)) {
            $this->byBundleSkuAndChildrenSourceItemsIdsIndexer->execute($bundleChildrenSourceItemsIdsWithSku);
        }
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

        if (count($bundleChildrenSourceItemsIdsWithSku)) {
            $this->byBundleSkuAndChildrenSourceItemsIdsIndexer->execute($bundleChildrenSourceItemsIdsWithSku);
        }
    }
}
