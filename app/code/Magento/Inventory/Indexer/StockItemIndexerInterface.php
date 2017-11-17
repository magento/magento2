<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Indexer;

use Magento\Framework\Indexer\ActionInterface;

/**
 * Extension pint for indexation
 *
 * @api
 */
interface StockItemIndexerInterface extends ActionInterface
{
    /**
     * Indexer ID in configuration
     */
    const INDEXER_ID = 'inventory_stock_item';
}
