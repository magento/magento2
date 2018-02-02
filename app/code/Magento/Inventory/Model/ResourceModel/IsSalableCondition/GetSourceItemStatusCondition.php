<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\ResourceModel\IsSalableCondition;

use Magento\Framework\DB\Select;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * Source Item status condition
 */
class GetSourceItemStatusCondition implements GetIsSalableConditionInterface
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
