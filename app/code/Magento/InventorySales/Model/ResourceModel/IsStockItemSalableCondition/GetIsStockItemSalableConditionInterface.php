<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ResourceModel\IsStockItemSalableCondition;

use Magento\Framework\DB\Select;

/**
 * Responsible for building is_salable conditions foe stock item
 *
 * @api
 */
interface GetIsStockItemSalableConditionInterface
{
    /**
     * @param Select $select
     * @return string
     */
    public function execute(Select $select): string;
}
