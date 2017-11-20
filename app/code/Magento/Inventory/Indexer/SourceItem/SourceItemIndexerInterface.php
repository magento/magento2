<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Indexer\SourceItem;

use Magento\Framework\Indexer\ActionInterface;

/**
 * Source Item indexer
 * Extension point for indexation
 *
 * @api
 */
interface SourceItemIndexerInterface extends ActionInterface
{
    /**
     * Indexer ID in configuration
     */
    const INDEXER_ID = 'inventory_source_item';
}
