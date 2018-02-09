<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleIndexer\Plugin\Indexer\SourceItem;

use Magento\InventoryBundleIndexer\Model\ResourceModel\BundleIndexer;
use Magento\InventoryBundleIndexer\Model\ResourceModel\GetAllBundleSourceItemsIds;
use Magento\InventoryBundleIndexer\Model\ResourceModel\GetBundleSourceItemsIds;

class BundleSourceItemIndexer
{
    /**
     * @var BundleIndexer
     */
    private $bundleIndexer;

    /**
     * @var GetBundleSourceItemsIds
     */
    private $getBundleSourceItemsIds;

    /**
     * @var GetAllBundleSourceItemsIds
     */
    private $getAllBundleSourceItemsIds;

    /**
     * @param BundleIndexer $bundleIndexer
     * @param GetBundleSourceItemsIds $getBundleSourceItemsIds
     * @param GetAllBundleSourceItemsIds $getAllBundleSourceItemsIds
     */
    public function __construct(
        BundleIndexer $bundleIndexer,
        GetBundleSourceItemsIds $getBundleSourceItemsIds,
        GetAllBundleSourceItemsIds $getAllBundleSourceItemsIds
    ) {
        $this->bundleIndexer = $bundleIndexer;
        $this->getBundleSourceItemsIds = $getBundleSourceItemsIds;
        $this->getAllBundleSourceItemsIds = $getAllBundleSourceItemsIds;
    }

    /**
     * @param \Magento\InventoryIndexer\Indexer\SourceItem\SourceItemIndexer $sourceItemIndexer
     * @param $result
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecuteFull(
        \Magento\InventoryIndexer\Indexer\SourceItem\SourceItemIndexer $sourceItemIndexer,
        $result
    ) {
        $bundleSourceItemsIds = $this->getAllBundleSourceItemsIds->execute();
        $this->bundleIndexer->execute($bundleSourceItemsIds);
    }

    /**
     * @param \Magento\InventoryIndexer\Indexer\SourceItem\SourceItemIndexer $subject
     * @param $result
     * @param array $sourceItemIds
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecuteList(
        \Magento\InventoryIndexer\Indexer\SourceItem\SourceItemIndexer $subject,
        $result,
        array $sourceItemIds
    ) {
        $bundleSourceItemsIds = $this->getBundleSourceItemsIds->execute($sourceItemIds);
        $this->bundleIndexer->execute($bundleSourceItemsIds);
    }
}
