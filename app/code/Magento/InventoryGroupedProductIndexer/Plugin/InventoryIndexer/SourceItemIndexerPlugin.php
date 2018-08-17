<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryGroupedProductIndexer\Plugin\InventoryIndexer;

use Magento\Framework\Exception\StateException;
use Magento\InventoryGroupedProductIndexer\Indexer\SourceItem\SourceItemIndexer as GroupedProductsSourceItemIndexer;
use Magento\InventoryIndexer\Indexer\SourceItem\SourceItemIndexer;

class SourceItemIndexerPlugin
{
    /**
     * @var GroupedProductsSourceItemIndexer
     */
    private $groupedProductsSourceItemIndexer;

    /**
     * @param GroupedProductsSourceItemIndexer $groupedProductsSourceItemIndexer
     */
    public function __construct(
        GroupedProductsSourceItemIndexer $groupedProductsSourceItemIndexer
    ) {
        $this->groupedProductsSourceItemIndexer = $groupedProductsSourceItemIndexer;
    }

    /**
     * @param SourceItemIndexer $subject
     * @param void $result
     * @param array $sourceItemIds
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws StateException
     */
    public function afterExecuteList(
        SourceItemIndexer $subject,
        $result,
        array $sourceItemIds
    ) {
        $this->groupedProductsSourceItemIndexer->executeList($sourceItemIds);
    }
}
