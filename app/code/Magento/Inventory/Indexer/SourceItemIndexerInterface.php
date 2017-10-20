<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Indexer;

use Magento\Framework\Indexer\ActionInterface;

/**
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
