<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Indexer\Stock;

use Magento\Framework\Indexer\ActionInterface;

/**
 * Stock indexer
 * Extension point for indexation
 *
 * @api
 */
interface StockIndexerInterface extends ActionInterface
{
    /**
     * Indexer ID in configuration
     */
    const INDEXER_ID = 'inventory_stock';
}
