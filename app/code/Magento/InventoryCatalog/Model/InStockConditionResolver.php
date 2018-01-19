<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\InventoryIndexer\Indexer\IndexStructure;

/**
 * Service to get in stock condition.
 */
class InStockConditionResolver
{
    /**
     * @param string $tableAlias
     * @return string
     */
    public function execute(string $tableAlias): string
    {
        return $tableAlias . '.' . IndexStructure::QUANTITY . ' > 0';
    }
}
