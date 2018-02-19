<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleIndexer\Plugin\InventoryIndexer\Indexer\SourceItem;

use Magento\InventoryBundleIndexer\Indexer\ExecuteFull;
use Magento\InventoryBundleIndexer\Indexer\ExecuteList;

class AddBundleDataToIndex
{
    /**
     * @var ExecuteList
     */
    private $executeList;

    /**
     * @var ExecuteFull
     */
    private $executeFull;

    /**
     * @param ExecuteList $executeList
     * @param ExecuteFull $executeFull
     */
    public function __construct(
        ExecuteList $executeList,
        ExecuteFull $executeFull
    ) {
        $this->executeList = $executeList;
        $this->executeFull = $executeFull;
    }

    /**
     * @param \Magento\InventoryIndexer\Indexer\SourceItem\SourceItemIndexer $sourceItemIndexer
     * @param $result
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecuteFull(
        \Magento\InventoryIndexer\Indexer\SourceItem\SourceItemIndexer $sourceItemIndexer,
        $result
    ) {
        $this->executeFull->execute();
    }

    /**
     * @param \Magento\InventoryIndexer\Indexer\SourceItem\SourceItemIndexer $subject
     * @param $result
     * @param array $sourceItemIds
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecuteList(
        \Magento\InventoryIndexer\Indexer\SourceItem\SourceItemIndexer $subject,
        $result,
        array $sourceItemIds
    ) {
        $this->executeList->execute($sourceItemIds);
    }
}
