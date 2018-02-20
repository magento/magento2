<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleIndexer\Plugin\InventoryIndexer\Indexer\SourceItem;

use Magento\InventoryBundleIndexer\Indexer\SourceItem\SourceItemIndexer as BundleSourceItemIndexer;
use Magento\InventoryIndexer\Indexer\SourceItem\SourceItemIndexer;

class AddBundleDataToIndexBySourceItemsIds
{
    /**
     * @var BundleSourceItemIndexer
     */
    private $sourceItemIndexer;

    /**
     * @param BundleSourceItemIndexer $sourceItemIndexer
     */
    public function __construct(
        BundleSourceItemIndexer $sourceItemIndexer
    ) {
        $this->sourceItemIndexer = $sourceItemIndexer;
    }

    /**
     * @param SourceItemIndexer $sourceItemIndexer
     * @param void $result
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecuteFull(
        SourceItemIndexer $sourceItemIndexer,
        $result
    ) {
        $this->sourceItemIndexer->executeFull();
    }

    /**
     * @param SourceItemIndexer $subject
     * @param void $result
     * @param array $sourceItemIds
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecuteList(
        SourceItemIndexer $subject,
        $result,
        array $sourceItemIds
    ) {
        $this->sourceItemIndexer->executeList($sourceItemIds);
    }
}
