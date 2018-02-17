<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleIndexer\Model\ResourceModel\Indexer;

class ExecuteFull
{
    /**
     * @var ReindexBySourceItemIds
     */
    private $reindexBySourceItemIds;

    /**
     * @var GetAllBundleChildrenSourceItemsIdsWithSku
     */
    private $getAllBundleChildrenSourceItemsIdsWithSku;

    /**
     * @param ReindexBySourceItemIds $reindexBySourceItemIds
     * @param GetAllBundleChildrenSourceItemsIdsWithSku $getAllBundleChildrenSourceItemsIdsWithSku
     */
    public function __construct(
        ReindexBySourceItemIds $reindexBySourceItemIds,
        GetAllBundleChildrenSourceItemsIdsWithSku $getAllBundleChildrenSourceItemsIdsWithSku
    ) {
        $this->reindexBySourceItemIds = $reindexBySourceItemIds;
        $this->getAllBundleChildrenSourceItemsIdsWithSku = $getAllBundleChildrenSourceItemsIdsWithSku;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $bundleChildrenSourceItemsIdsBySku = $this->getAllBundleChildrenSourceItemsIdsWithSku->execute();
        $this->reindexBySourceItemIds->execute($bundleChildrenSourceItemsIdsBySku);
    }
}
