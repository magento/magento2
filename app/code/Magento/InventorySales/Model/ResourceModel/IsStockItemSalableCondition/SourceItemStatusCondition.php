<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ResourceModel\IsStockItemSalableCondition;

use Magento\Framework\DB\Select;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * Source Item status condition
 */
class SourceItemStatusCondition implements GetIsStockItemSalableConditionInterface
{
    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Select $select): string
    {
        return 'MAX(source_item.' . SourceItemInterface::STATUS . ') = ' . SourceItemInterface::STATUS_IN_STOCK;
    }
}
