<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleIndexer\Model\ResourceModel\Indexer;

class ExecuteList
{
    /**
     * @var ReindexBySourceItemIds
     */
    private $reindexBySourceItemIds;

    /**
     * @var GetBundleChildrenSourceItemsIdsWithSku
     */
    private $getBundleChildrenSourceItemsIdsBySku;

    /**
     * @param ReindexBySourceItemIds $reindexBySourceItemIds
     * @param GetBundleChildrenSourceItemsIdsWithSku $getBundleChildrenSourceItemsIdsBySku
     */
    public function __construct(
        ReindexBySourceItemIds $reindexBySourceItemIds,
        GetBundleChildrenSourceItemsIdsWithSku $getBundleChildrenSourceItemsIdsBySku
    ) {
        $this->reindexBySourceItemIds = $reindexBySourceItemIds;
        $this->getBundleChildrenSourceItemsIdsBySku = $getBundleChildrenSourceItemsIdsBySku;
    }

    /**
     * @param array $sourceItemIds
     * @return void
     */
    public function execute(array $sourceItemIds)
    {
        $bundleChildrenSourceItemsIdsBySku = $this->getBundleChildrenSourceItemsIdsBySku->execute($sourceItemIds);
        $this->reindexBySourceItemIds->execute($bundleChildrenSourceItemsIdsBySku);
    }
}
