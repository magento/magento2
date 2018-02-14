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
     * @var GetBundleSourceItemsIds
     */
    private $getBundleSourceItemsIds;

    /**
     * @param ReindexBySourceItemIds $reindexBySourceItemIds
     * @param GetBundleSourceItemsIds $getBundleSourceItemsIds
     */
    public function __construct(
        ReindexBySourceItemIds $reindexBySourceItemIds,
        GetBundleSourceItemsIds $getBundleSourceItemsIds
    ) {
        $this->reindexBySourceItemIds = $reindexBySourceItemIds;
        $this->getBundleSourceItemsIds = $getBundleSourceItemsIds;
    }

    /**
     * @param array $sourceItemIds
     * @return void
     */
    public function execute(array $sourceItemIds)
    {
        $bundleSourceItemsIds = $this->getBundleSourceItemsIds->execute($sourceItemIds);
        $this->reindexBySourceItemIds->execute($bundleSourceItemsIds);
    }
}
