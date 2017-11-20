<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Indexer\Source;

use Magento\Framework\Indexer\ActionInterface;

/**
 * Source indexer
 * Extension point for indexation
 *
 * @api
 */
interface SourceIndexerInterface extends ActionInterface
{
    /**
     * Indexer ID in configuration
     */
    const INDEXER_ID = 'inventory_source';
}
