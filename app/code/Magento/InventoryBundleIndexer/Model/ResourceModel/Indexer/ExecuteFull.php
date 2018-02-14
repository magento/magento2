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
     * @var GetAllBundleSourceItemsIds
     */
    private $getAllBundleSourceItemsIds;

    /**
     * @param ReindexBySourceItemIds $reindexBySourceItemIds
     * @param GetAllBundleSourceItemsIds $getAllBundleSourceItemsIds
     */
    public function __construct(
        ReindexBySourceItemIds $reindexBySourceItemIds,
        GetAllBundleSourceItemsIds $getAllBundleSourceItemsIds
    ) {
        $this->reindexBySourceItemIds = $reindexBySourceItemIds;
        $this->getAllBundleSourceItemsIds = $getAllBundleSourceItemsIds;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $bundleSourceItemsIds = $this->getAllBundleSourceItemsIds->execute();
        $this->reindexBySourceItemIds->execute($bundleSourceItemsIds);
    }
}
