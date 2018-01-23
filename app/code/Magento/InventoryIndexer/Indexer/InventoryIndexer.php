<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Indexer;

use Magento\Framework\Indexer\ActionInterface;
use Magento\InventoryIndexer\Indexer\SourceItem\SourceItemIndexer;

/**
 * Inventory indexer
 *
 * @api
 */
class InventoryIndexer implements ActionInterface
{
    /**
     * Indexer ID in configuration
     */
    const INDEXER_ID = 'inventory';

    /**
     * @var SourceItemIndexer
     */
    private $sourceItemIndexer;

    /**
     * @param SourceItemIndexer $sourceItemIndexer
     */
    public function __construct(
        SourceItemIndexer $sourceItemIndexer
    ) {
        $this->sourceItemIndexer = $sourceItemIndexer;
    }

    /**
     * @inheritdoc
     */
    public function executeFull()
    {
        $this->sourceItemIndexer->executeFull();
    }

    /**
     * @inheritdoc
     */
    public function executeRow($sourceItemId)
    {
        $this->sourceItemIndexer->executeList([$sourceItemId]);
    }

    /**
     * @inheritdoc
     */
    public function executeList(array $sourceItemIds)
    {
        $this->sourceItemIndexer->executeList($sourceItemIds);
    }
}
